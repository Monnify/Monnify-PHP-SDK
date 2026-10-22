<?php

namespace Monnify\Tests;

use Monnify\Services\InvoiceService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvoiceServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $payload = $this->validPayload() + [
            'incomeSplitConfig' => null,
            'redirectUrl' => null,
        ];
        $service = new InvoiceService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));

        $service->create($payload);
        $service->get('inv-123');
        $service->all();
        $service->cancel('inv-123');
        $service->attachReservedAccount($payload);

        $this->assertRequest(1, 'POST', '/api/v1/invoice/create');
        $this->assertSame($payload, $this->requestJson(1));
        $this->assertRequest(2, 'GET', '/api/v1/invoice/inv-123/details');
        $this->assertRequest(3, 'GET', '/api/v1/invoice/all');
        $this->assertRequest(4, 'DELETE', '/api/v1/invoice/inv-123/cancel');
        $this->assertRequest(5, 'POST', '/api/v1/invoice/create');
        $this->assertSame($payload, $this->requestJson(5));
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'amount' => 5000,
            'currencyCode' => 'NGN',
            'invoiceReference' => 'inv-123',
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'contractCode' => 'contract-123',
            'description' => 'Invoice payment',
            'expiryDate' => '2026-12-31',
        ];
    }
}
