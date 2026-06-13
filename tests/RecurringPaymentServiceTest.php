<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\RecurringPaymentService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RecurringPaymentServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_posts_the_expected_payload(): void
    {
        $service = new RecurringPaymentService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
        ]));
        $payload = $this->validPayload();

        $service->chargeCardToken($payload);

        $this->assertRequest(1, 'POST', '/api/v1/merchant/cards/charge-card-token');
        $this->assertSame($payload, $this->requestJson(1));
    }

    #[Test]
    public function it_requires_card_token(): void
    {
        $service = new RecurringPaymentService($this->apiClient([]));
        $payload = $this->validPayload();
        unset($payload['cardToken']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cardToken is required');

        $service->chargeCardToken($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'amount' => 5000,
            'cardToken' => 'card-token',
            'customerEmail' => 'jane@example.com',
            'paymentReference' => 'payment-123',
            'contractCode' => 'contract-123',
            'apiKey' => 'api-key',
        ];
    }
}
