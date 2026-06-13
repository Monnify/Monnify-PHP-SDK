<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\SubAccountService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SubAccountServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new SubAccountService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $payload = [$this->validPayload()];

        $service->create($payload);
        $service->all();
        $service->update($this->validPayload() + ['subAccountCode' => 'sub-123']);
        $service->delete('sub-123');

        $this->assertRequest(1, 'POST', '/api/v1/sub-accounts');
        $this->assertSame($payload, $this->requestJson(1));
        $this->assertRequest(2, 'GET', '/api/v1/sub-accounts');
        $this->assertRequest(3, 'PUT', '/api/v1/sub-accounts');
        $this->assertRequest(4, 'DELETE', '/api/v1/sub-accounts/sub-123');
    }

    #[Test]
    public function update_requires_default_split_percentage(): void
    {
        $service = new SubAccountService($this->apiClient([]));
        $payload = $this->validPayload();
        unset($payload['defaultSplitPercentage']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('defaultSplitPercentage is required');

        $service->update($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'currencyCode' => 'NGN',
            'accountNumber' => '0123456789',
            'bankCode' => '058',
            'email' => 'jane@example.com',
            'defaultSplitPercentage' => 20,
        ];
    }
}
