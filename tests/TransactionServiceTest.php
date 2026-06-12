<?php

namespace Monnify\Tests;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Monnify\Services\TransactionService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransactionServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function initialisePostsTheExpectedPayload(): void
    {
        $payload = $this->validInitializePayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $result = $service->initialise($payload);

        $this->assertSame(['ok' => true], $result);
        $this->assertSame('/api/v1/merchant/transactions/init-transaction', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('POST', $this->history[1]['request']->getMethod());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function payWithBankTransferPostsTheExpectedPayload(): void
    {
        $payload = ['transactionReference' => 'txn-ref', 'bankCode' => '058'];
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->payWithBankTransfer($payload);

        $this->assertSame('/api/v1/merchant/bank-transfer/init-payment', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function chargeCardPostsTheExpectedPayload(): void
    {
        $payload = $this->validCardPayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->chargeCard($payload);

        $this->assertSame('/api/v1/merchant/cards/charge', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function authorizeOtpPostsTheExpectedPayload(): void
    {
        $payload = [
            'transactionReference' => 'txn-ref',
            'collectionChannel' => 'API_NOTIFICATION',
            'tokenId' => 'token-id',
            'token' => '123456',
        ];
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->authorizeOTP($payload);

        $this->assertSame('/api/v1/merchant/cards/otp/authorize', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function authorizeThreeDsCardPostsTheExpectedPayload(): void
    {
        $payload = $this->validThreeDsPayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->authorizeThreeDSCard($payload);

        $this->assertSame('/api/v1/sdk/cards/secure-3d/authorize', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function allAddsQueryParametersToSearchRequest(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->all([
            'page' => 2,
            'size' => 20,
            'paymentReference' => 'pay-ref',
        ]);

        $this->assertSame('/api/v1/transactions/search', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('page=2&size=20&paymentReference=pay-ref', $this->history[1]['request']->getUri()->getQuery());
    }

    #[Test]
    public function statusRequiresTransactionReference(): void
    {
        $service = $this->service([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction Reference must be provided');

        $service->status('');
    }

    #[Test]
    public function statusUsesTransactionStatusEndpoint(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->status('txn-ref');

        $this->assertSame('/api/v2/transactions/txn-ref', $this->history[1]['request']->getUri()->getPath());
    }

    #[Test]
    public function statusByReferenceSupportsTransactionReferences(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->statusByReference('txn-ref');

        $this->assertSame('/api/v2/merchant/transactions/query', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('transactionReference=txn-ref', $this->history[1]['request']->getUri()->getQuery());
    }

    #[Test]
    public function statusByReferenceSupportsPaymentReferences(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->statusByReference('pay-ref', 'payment');

        $this->assertSame('paymentReference=pay-ref', $this->history[1]['request']->getUri()->getQuery());
    }

    #[Test]
    public function statusByReferenceRejectsUnknownReferenceTypes(): void
    {
        $service = $this->service([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Either transaction or payment must be provided as referenceType');

        $service->statusByReference('txn-ref', 'invoice');
    }

    #[Test]
    public function statusByReferenceRequiresReference(): void
    {
        $service = $this->service([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reference must be provided');

        $service->statusByReference('');
    }

    #[Test]
    public function initialiseRequiresAValidPayload(): void
    {
        $service = $this->service([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('amount is required');

        $service->initialise([]);
    }

    /**
     * @param array<int, mixed> $responses
     */
    private function service(array $responses): TransactionService
    {
        return new TransactionService($this->apiClient($responses));
    }

    private function validInitializePayload(): array
    {
        return [
            'amount' => 5000,
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'paymentReference' => 'pay-ref',
            'paymentDescription' => 'Invoice payment',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-code',
            'redirectUrl' => 'https://example.com/return',
        ];
    }

    private function validCardPayload(): array
    {
        return [
            'transactionReference' => 'txn-ref',
            'collectionChannel' => 'API_NOTIFICATION',
            'card' => [
                'number' => '4242424242424242',
                'pin' => '1234',
                'expiryMonth' => '09',
                'expiryYear' => '29',
                'cvv' => '123',
            ],
        ];
    }

    private function validThreeDsPayload(): array
    {
        return $this->validCardPayload() + [
            'apiKey' => 'api-key',
            'deviceInformation' => [
                'httpBrowserLanguage' => 'en-US',
                'httpBrowserJavaEnabled' => false,
                'httpBrowserJavaScriptEnabled' => true,
                'httpBrowserColorDepth' => '24',
                'httpBrowserScreenHeight' => '1080',
                'httpBrowserScreenWidth' => '1920',
                'httpBrowserTimeDifference' => '-60',
                'userAgentBrowserValue' => 'Mozilla/5.0',
            ],
        ];
    }

}
