<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\WalletService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WalletServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new WalletService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $payload = [
            'walletReference' => 'wallet-123',
            'walletName' => 'Main Wallet',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
        ];

        $service->create($payload);
        $service->get('jane@example.com', 20, 2);
        $service->balance('0123456789');
        $service->transactions('0123456789', 50, 3);

        $this->assertRequest(1, 'POST', '/api/v1/disbursements/wallet');
        $this->assertSame($payload, $this->requestJson(1));
        $this->assertRequest(2, 'GET', '/api/v1/disbursements/wallet', 'customerEmail=jane%40example.com&pageSize=20&pageNo=2');
        $this->assertRequest(3, 'GET', '/api/v1/disbursements/wallet/balance', 'accountNumber=0123456789');
        $this->assertRequest(4, 'GET', '/api/v1/disbursements/wallet/transactions', 'accountNumber=0123456789&pageSize=50&pageNo=3');
    }

    #[Test]
    public function balance_requires_account_number(): void
    {
        $service = new WalletService($this->apiClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account Number must provided.');

        $service->balance('');
    }
}
