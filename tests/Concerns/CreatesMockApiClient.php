<?php

namespace Monnify\Tests\Concerns;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Monnify\Contracts\HttpClientInterface;
use Monnify\Http\GuzzleHttpClient;
use Monnify\Http\MonnifyApiClient;
use Monnify\MonnifyConfig;

trait CreatesMockApiClient
{
    private array $history = [];

    /**
     * @param array<int, mixed> $responses
     */
    private function apiClient(array $responses): MonnifyApiClient
    {
        return new MonnifyApiClient($this->config(), $this->client($responses));
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

    private function authResponse(): Response
    {
        return new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']]));
    }

    private function okResponse(): Response
    {
        return new Response(200, [], $this->json(['ok' => true]));
    }

    private function assertRequest(int $index, string $method, string $path, string $query = ''): void
    {
        $this->assertSame($method, $this->history[$index]['request']->getMethod());
        $this->assertSame($path, $this->history[$index]['request']->getUri()->getPath());
        $this->assertSame($query, $this->history[$index]['request']->getUri()->getQuery());
    }

    /**
     * @return array<array-key, mixed>
     */
    private function requestJson(int $index): array
    {
        return json_decode((string) $this->history[$index]['request']->getBody(), true);
    }

    private function config(): MonnifyConfig
    {
        return MonnifyConfig::sandbox('api-key', 'secret-key', 'contract-code');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function json(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
