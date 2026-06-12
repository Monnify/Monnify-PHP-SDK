<?php

namespace Monnify\Tests;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Monnify\Monnify;
use Monnify\Services\CustomerReservedAccountService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CustomerReservedAccountServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function createGeneralAccountPostsTheExpectedPayload(): void
    {
        $payload = $this->validGeneralAccountPayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $result = $service->createGeneralAccount($payload);

        $this->assertSame(['ok' => true], $result);
        $this->assertSame('/api/v2/bank-transfer/reserved-accounts', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('POST', $this->history[1]['request']->getMethod());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function createInvoiceAccountPostsTheExpectedPayload(): void
    {
        $payload = $this->validInvoiceAccountPayload();
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->createInvoiceAccount($payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function getUsesTheExpectedEndpoint(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->get('acct-ref');

        $this->assertSame('/api/v2/bank-transfer/reserved-accounts/acct-ref', $this->history[1]['request']->getUri()->getPath());
    }

    #[Test]
    public function addLinkedAccountsUsesTheExpectedEndpointAndPayload(): void
    {
        $payload = ['getAllAvailableBanks' => false, 'preferredBanks' => ['058', '011']];
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->addLinkedAccounts('acct-ref', $payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/add-linked-accounts/acct-ref', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('PUT', $this->history[1]['request']->getMethod());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function updateBvnUsesTheExpectedPayload(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->updateBVN('acct-ref', '12345678901');

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/update-customer-bvn/acct-ref', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame(['bvn' => '12345678901'], json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function allowedPaymentSourceUsesTheExpectedEndpointAndPayload(): void
    {
        $payload = ['restrictPaymentSource' => true, 'allowedPaymentSource' => ['bvns' => ['12345678901']]];
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->allowedPaymentSource('acct-ref', $payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/update-payment-source-filter/acct-ref', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function updateSplitConfigUsesTheExpectedEndpointAndPayload(): void
    {
        $payload = [['subAccountCode' => 'sub-123', 'feeBearer' => true, 'feePercentage' => 0.5, 'splitPercentage' => 20]];
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->updateSplitConfig('acct-ref', $payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/update-income-split-config/acct-ref', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function deallocateAccountUsesTheExpectedEndpoint(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->deallocateAccount('acct-ref');

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/reference/acct-ref', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('DELETE', $this->history[1]['request']->getMethod());
    }

    #[Test]
    public function transactionsAddsTheExpectedQueryParameters(): void
    {
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->transactions('acct-ref', ['page' => 2, 'size' => 25]);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/transactions', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('accountReference=acct-ref&page=2&size=25', $this->history[1]['request']->getUri()->getQuery());
    }

    #[Test]
    public function updateKycInfoUsesTheExpectedEndpointAndPayload(): void
    {
        $payload = ['bvn' => '12345678901'];
        $service = $this->service([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]);

        $service->updateKYCInfo('acct-ref', $payload);

        $this->assertSame('/api/v1/bank-transfer/reserved-accounts/acct-ref/kyc-info', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame($payload, json_decode((string) $this->history[1]['request']->getBody(), true));
    }

    #[Test]
    public function accountReferenceIsRequired(): void
    {
        $service = $this->service([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account Reference must be provided');

        $service->get('');
    }

    #[Test]
    public function customerReservedAccountsAccessorUsesConfiguredContractCode(): void
    {
        $monnify = new Monnify($this->config(), $this->client([
            new Response(200, [], $this->json(['responseBody' => ['accessToken' => 'token-123']])),
            new Response(200, [], $this->json(['ok' => true])),
        ]));

        $payload = $this->validGeneralAccountPayload();
        unset($payload['contractCode']);

        $monnify->customerReservedAccounts()->createGeneralAccount($payload);

        $body = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertSame('contract-code', $body['contractCode']);
    }

    /**
     * @param array<int, mixed> $responses
     */
    private function service(array $responses): CustomerReservedAccountService
    {
        return new CustomerReservedAccountService($this->apiClient($responses));
    }

    private function validGeneralAccountPayload(): array
    {
        return [
            'accountReference' => 'acct-ref',
            'accountName' => 'Main account',
            'currencyCode' => 'NGN',
            'contractCode' => 'contract-code',
            'customerEmail' => 'jane@example.com',
            'customerName' => 'Jane Doe',
            'getAllAvailableBanks' => true,
            'restrictPaymentSource' => false,
            'bvn' => '12345678901',
        ];
    }

    private function validInvoiceAccountPayload(): array
    {
        return [
            'contractCode' => 'contract-code',
            'accountName' => 'Invoice account',
            'currencyCode' => 'NGN',
            'accountReference' => 'acct-ref',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
        ];
    }

}
