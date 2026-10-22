<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\PayCodeService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PayCodeServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new PayCodeService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $payload = $this->validPayload();

        $service->create($payload);
        $service->history(['transactionReference' => 'txn-123', 'from' => 1715068800000]);
        $service->get('paycode-123');
        $service->getUnMasked('paycode-123');
        $service->delete('paycode-123');

        $this->assertRequest(1, 'POST', '/api/v1/paycode');
        $this->assertSame($payload, $this->requestJson(1));
        $this->assertRequest(2, 'GET', '/api/v1/paycode', 'transactionReference=txn-123&from=1715068800000');
        $this->assertRequest(3, 'GET', '/api/v1/paycode/paycode-123');
        $this->assertRequest(4, 'GET', '/api/v1/paycode/paycode-123/authorize');
        $this->assertRequest(5, 'DELETE', '/api/v1/paycode/paycode-123');
    }

    #[Test]
    public function create_requires_minimum_amount(): void
    {
        $service = new PayCodeService($this->apiClient([]));
        $payload = $this->validPayload();
        $payload['amount'] = 10;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('amount must be at least 20');

        $service->create($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'beneficiaryName' => 'Jane Doe',
            'amount' => 5000,
            'paycodeReference' => 'paycode-123',
            'expiryDate' => '2026-12-31',
            'clientId' => 'client-123',
        ];
    }
}
