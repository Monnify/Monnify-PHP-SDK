<?php

namespace Monnify\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monnify\Auth\InMemoryTokenCache;
use Monnify\Contracts\HttpClientInterface;
use Monnify\Environment;
use Monnify\Http\GuzzleHttpClient;
use Monnify\Http\MonnifyApiClient;
use Monnify\Monnify;
use Monnify\MonnifyConfig;
use Monnify\MonnifyException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MonnifyTest extends TestCase
{
    private array $history = [];

    #[Test]
    public function constructorDoesNotAuthenticateImmediately(): void
    {
        new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['unused' => true])),
        ]));

        $this->assertSame([], $this->history);
    }

    #[Test]
    public function initializeTransactionAuthenticatesLazilyAndSendsJsonPayload(): void
    {
        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['requestSuccessful' => true])),
        ]));

        $response = $monnify->initializeTransaction([
            'amount' => 5000,
            'customerEmail' => 'customer@example.com',
            'paymentReference' => 'payment-reference',
            'currencyCode' => 'NGN',
        ]);

        $this->assertSame(['requestSuccessful' => true], $response);
        $this->assertCount(2, $this->history);

        $authRequest = $this->history[0]['request'];
        $this->assertSame('POST', $authRequest->getMethod());
        $this->assertSame('/api/v1/auth/login', $authRequest->getUri()->getPath());
        $this->assertSame(
            'Basic ' . base64_encode('api-key:secret-key'),
            $authRequest->getHeaderLine('Authorization')
        );

        $transactionRequest = $this->history[1]['request'];
        $this->assertSame('POST', $transactionRequest->getMethod());
        $this->assertSame('/api/v1/merchant/transactions/init-transaction', $transactionRequest->getUri()->getPath());
        $this->assertSame('Bearer token-123', $transactionRequest->getHeaderLine('Authorization'));
        $this->assertSame(
            [
                'amount' => 5000,
                'customerEmail' => 'customer@example.com',
                'paymentReference' => 'payment-reference',
                'currencyCode' => 'NGN',
                'contractCode' => 'contract-code',
            ],
            json_decode((string) $transactionRequest->getBody(), true)
        );
    }

    #[Test]
    public function callerSuppliedContractCodeIsPreserved(): void
    {
        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['requestSuccessful' => true])),
        ]));

        $monnify->initializeTransaction([
            'amount' => 5000,
            'customerEmail' => 'customer@example.com',
            'paymentReference' => 'payment-reference',
            'currencyCode' => 'NGN',
            'contractCode' => 'override-contract-code',
        ]);

        $request = $this->history[1]['request'];

        $this->assertSame(
            [
                'amount' => 5000,
                'customerEmail' => 'customer@example.com',
                'paymentReference' => 'payment-reference',
                'currencyCode' => 'NGN',
                'contractCode' => 'override-contract-code',
            ],
            json_decode((string) $request->getBody(), true)
        );
    }

    #[Test]
    public function bearerTokenIsReusedAcrossRequests(): void
    {
        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['banks' => []])),
            new Response(200, [], $this->json(['banks' => []])),
        ]));

        $monnify->getAllBanks();
        $monnify->getAllBanks();

        $this->assertCount(3, $this->history);
        $this->assertSame('/api/v1/auth/login', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('/api/v1/banks', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('/api/v1/banks', $this->history[2]['request']->getUri()->getPath());
    }

    #[Test]
    public function getAllTransactionsSendsQueryParameters(): void
    {
        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['requestSuccessful' => true])),
        ]));

        $monnify->getAllTransactions(2, 50, ['paymentStatus' => 'PAID']);

        $request = $this->history[1]['request'];

        $this->assertSame('/api/v1/transactions/search', $request->getUri()->getPath());
        $this->assertSame('page=2&size=50&paymentStatus=PAID', $request->getUri()->getQuery());
    }

    #[Test]
    public function webhookValidationUsesConfiguredSecret(): void
    {
        $monnify = new Monnify($this->config(), $this->client([]));
        $body = '{"eventType":"SUCCESSFUL_TRANSACTION"}';

        $validHash = hash_hmac('sha512', $body, 'secret-key');

        $this->assertTrue($monnify->validateWebhook($body, $validHash));
        $this->assertFalse($monnify->validateWebhook($body, 'invalid-hash'));
    }

    #[Test]
    public function configCanBeCreatedFromLaravelFriendlyArray(): void
    {
        $config = MonnifyConfig::fromArray([
            'api_key' => 'api-key',
            'secret_key' => 'secret-key',
            'contract_code' => 'contract-code',
            'environment' => 'live',
        ]);

        $this->assertSame(Environment::Live, $config->environment);
        $this->assertSame('https://api.monnify.com', $config->baseUrl());
    }

    #[Test]
    public function customBaseUrlCanBeConfigured(): void
    {
        $config = MonnifyConfig::sandbox(
            apiKey: 'api-key',
            secretKey: 'secret-key',
            contractCode: 'contract-code',
            apiUrl: 'https://example.test/',
        );

        $this->assertSame('https://example.test', $config->baseUrl());
    }

    #[Test]
    public function injectedTokenCacheAvoidsAuthenticationRequest(): void
    {
        $cache = new InMemoryTokenCache();
        $cache->put('cached-token', 3600);

        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['banks' => []])),
        ]), $cache);

        $monnify->getAllBanks();

        $this->assertCount(1, $this->history);
        $this->assertSame('/api/v1/banks', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('Bearer cached-token', $this->history[0]['request']->getHeaderLine('Authorization'));
    }

    #[Test]
    public function expiredTokenCacheFetchesNewToken(): void
    {
        $cache = new InMemoryTokenCache();
        $cache->put('expired-token', -1);

        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'fresh-token', 'expiresIn' => 120]])),
            new Response(200, [], $this->json(['banks' => []])),
        ]), $cache);

        $monnify->getAllBanks();

        $this->assertCount(2, $this->history);
        $this->assertSame('/api/v1/auth/login', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('fresh-token', $cache->get());
    }

    #[Test]
    public function missingAccessTokenRaisesSdkException(): void
    {
        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => []])),
        ]));

        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('access token');

        $monnify->getAllBanks();
    }

    #[Test]
    public function networkFailureRaisesSdkException(): void
    {
        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new ConnectException('Connection timed out', new Request('GET', '/api/v1/banks')),
        ]));

        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Monnify HTTP request failed');

        $monnify->getAllBanks();
    }

    #[Test]
    public function httpErrorRaisesSdkExceptionWithResponseMetadata(): void
    {
        $client = $this->client([
            new Response(400, [], $this->json(['requestSuccessful' => false, 'responseMessage' => 'Bad request'])),
        ]);

        try {
            $client->request('GET', '/api/v1/banks');
            $this->fail('Expected MonnifyException to be thrown.');
        } catch (MonnifyException $e) {
            $this->assertSame(400, $e->statusCode());
            $this->assertSame(
                ['requestSuccessful' => false, 'responseMessage' => 'Bad request'],
                $e->responseBody()
            );
            $this->assertSame(
                $this->json(['requestSuccessful' => false, 'responseMessage' => 'Bad request']),
                $e->rawResponseBody()
            );
        }
    }

    #[Test]
    public function nonJsonHttpErrorPreservesStatusAndRawBody(): void
    {
        $client = $this->client([
            new Response(502, [], 'Bad Gateway'),
        ]);

        try {
            $client->request('GET', '/api/v1/banks');
            $this->fail('Expected MonnifyException to be thrown.');
        } catch (MonnifyException $e) {
            $this->assertSame(502, $e->statusCode());
            $this->assertNull($e->responseBody());
            $this->assertSame('Bad Gateway', $e->rawResponseBody());
        }
    }

    #[Test]
    public function invalidJsonResponseRaisesSdkException(): void
    {
        $client = $this->client([
            new Response(200, [], 'not-json'),
        ]);

        $this->expectException(MonnifyException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $client->request('GET', '/api/v1/banks');
    }

    #[Test]
    public function emptyResponseBodyReturnsEmptyArray(): void
    {
        $client = $this->client([
            new Response(204, [], ''),
        ]);

        $this->assertSame([], $client->request('GET', '/api/v1/banks'));
    }

    #[Test]
    public function apiClientRetriesOnceAfterUnauthorizedResponse(): void
    {
        $cache = new InMemoryTokenCache();
        $cache->put('stale-token', 3600);

        $client = new MonnifyApiClient($this->config(), $this->client([
            new Response(401, [], $this->json(['responseMessage' => 'Unauthorized'])),
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'fresh-token', 'expiresIn' => 120]])),
            new Response(200, [], $this->json(['banks' => []])),
        ]), $cache);

        $response = $client->request('GET', '/api/v1/banks');

        $this->assertSame(['banks' => []], $response);
        $this->assertCount(3, $this->history);
        $this->assertSame('Bearer stale-token', $this->history[0]['request']->getHeaderLine('Authorization'));
        $this->assertSame('/api/v1/auth/login', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('Bearer fresh-token', $this->history[2]['request']->getHeaderLine('Authorization'));
    }

    #[Test]
    public function apiClientSendsAuthenticatedRequests(): void
    {
        $client = new MonnifyApiClient($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]));

        $response = $client->request('PATCH', '/custom-endpoint', ['name' => 'Jane'], ['page' => 1]);

        $this->assertSame(['ok' => true], $response);
        $request = $this->history[1]['request'];
        $this->assertSame('PATCH', $request->getMethod());
        $this->assertSame('/custom-endpoint', $request->getUri()->getPath());
        $this->assertSame('page=1', $request->getUri()->getQuery());
        $this->assertSame(['name' => 'Jane'], json_decode((string) $request->getBody(), true));
    }

    /**
     * @param array<int, mixed> $responses
     */
    private function client(array $responses): HttpClientInterface
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history));

        $client = new Client([
            'base_uri' => 'https://sandbox.monnify.com',
            'handler' => $stack,
        ]);

        return new GuzzleHttpClient('https://sandbox.monnify.com', $client);
    }

    private function config(): MonnifyConfig
    {
        return MonnifyConfig::sandbox('api-key', 'secret-key', 'contract-code');
    }

    private function json(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
