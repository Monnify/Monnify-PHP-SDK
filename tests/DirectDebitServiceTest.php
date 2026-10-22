<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\DirectDebitService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DirectDebitServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new DirectDebitService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $debitPayload = [
            'paymentReference' => 'payment-123',
            'mandateCode' => 'mandate-code',
            'narration' => 'Subscription charge',
            'customerEmail' => 'jane@example.com',
        ];

        $service->create($this->validMandatePayload());
        $service->get('mandate-123');
        $service->debit($debitPayload);
        $service->status('payment-123');
        $service->cancel('mandate-code');

        $this->assertRequest(1, 'POST', '/api/v1/direct-debit/mandate/create');
        $this->assertSame($this->validMandatePayload(), $this->requestJson(1));
        $this->assertRequest(2, 'GET', '/api/v1/direct-debit/mandate/', 'mandateReferences=mandate-123');
        $this->assertRequest(3, 'POST', '/api/v1/direct-debit/mandate/debit');
        $this->assertSame($debitPayload, $this->requestJson(3));
        $this->assertRequest(4, 'GET', '/api/v1/direct-debit/mandate/debit-status', 'paymentReference=payment-123');
        $this->assertRequest(5, 'PATCH', '/api/v1/direct-debit/mandate/cancel-mandate/mandate-code');
    }

    #[Test]
    public function get_requires_mandate_reference(): void
    {
        $service = new DirectDebitService($this->apiClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mandate Reference must be provided.');

        $service->get('');
    }

    /**
     * @return array<string, mixed>
     */
    private function validMandatePayload(): array
    {
        return [
            'contractCode' => 'contract-123',
            'mandateReference' => 'mandate-123',
            'customerName' => 'Jane Doe',
            'customerEmailAddress' => 'jane@example.com',
            'customerPhoneNumber' => '08012345678',
            'customerAddress' => '12 Broad Street',
            'customerAccountNumber' => '0123456789',
            'customerAccountBankCode' => '058',
            'mandateDescription' => 'Monthly subscription',
            'mandateStartDate' => '2026-05-01',
            'mandateEndDate' => '2026-12-31',
        ];
    }
}
