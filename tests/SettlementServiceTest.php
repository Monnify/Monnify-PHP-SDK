<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\SettlementService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SettlementServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new SettlementService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));

        $service->transactions('settlement-123', 10, 1);
        $service->getByTransaction('txn-123');

        $this->assertRequest(1, 'GET', '/api/v1/transactions/find-by-settlement-reference', 'reference=settlement-123&size=10&page=1');
        $this->assertRequest(2, 'GET', '/api/v1/settlement-detail', 'transactionReference=txn-123');
    }

    #[Test]
    public function get_by_transaction_requires_reference(): void
    {
        $service = new SettlementService($this->apiClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction Reference must be provided.');

        $service->getByTransaction('');
    }
}
