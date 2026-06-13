<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\RefundService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RefundServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new RefundService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $payload = [
            'transactionReference' => 'txn-123',
            'refundAmount' => 5000,
            'refundReference' => 'refund-123',
            'refundReason' => 'Customer request',
            'customerNote' => 'Refund approved',
        ];

        $service->initialise($payload);
        $service->all(25, 2);
        $service->status('refund-123');

        $this->assertRequest(1, 'POST', '/api/v1/refunds/initiate-refund');
        $this->assertSame($payload, $this->requestJson(1));
        $this->assertRequest(2, 'GET', '/api/v1/refunds', 'size=25&page=2');
        $this->assertRequest(3, 'GET', '/api/v1/refunds/refund-123');
    }

    #[Test]
    public function initialise_requires_refund_amount(): void
    {
        $service = new RefundService($this->apiClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('refundAmount is required');

        $service->initialise([
            'transactionReference' => 'txn-123',
            'refundReference' => 'refund-123',
            'refundReason' => 'Customer request',
            'customerNote' => 'Refund approved',
        ]);
    }
}
