<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\BillsPaymentService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BillsPaymentServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new BillsPaymentService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $customerPayload = ['productCode' => 'PROD-001', 'customerId' => 'CUST-123'];
        $vendPayload = [
            'productCode' => 'PROD-001',
            'customerId' => 'CUST-123',
            'vendAmount' => 1000,
            'vendReference' => 'vend-ref-123',
        ];

        $service->categories(20, 2);
        $service->billers('ELECTRICITY', 15, 1);
        $service->products('BILLER-001');
        $service->validateCustomer($customerPayload);
        $service->vend($vendPayload);
        $service->requery('vend-ref-123');

        $this->assertRequest(1, 'GET', '/api/v1/vas/bills-payment/biller-categories', 'size=20&page=2');
        $this->assertRequest(2, 'GET', '/api/v1/vas/bills-payment/billers', 'size=15&page=1&category_code=ELECTRICITY');
        $this->assertRequest(3, 'GET', '/api/v1/vas/bills-payment/biller-products', 'biller_code=BILLER-001&size=10&page=0');
        $this->assertRequest(4, 'POST', '/api/v1/vas/bills-payment/validate-customer');
        $this->assertSame($customerPayload, $this->requestJson(4));
        $this->assertRequest(5, 'POST', '/api/v1/vas/bills-payment/vend');
        $this->assertSame($vendPayload, $this->requestJson(5));
        $this->assertRequest(6, 'GET', '/api/v1/vas/bills-payment/requery', 'vendReference=vend-ref-123');
    }

    #[Test]
    public function requery_requires_vend_reference(): void
    {
        $service = new BillsPaymentService($this->apiClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('vendReference is required');

        $service->requery('');
    }
}
