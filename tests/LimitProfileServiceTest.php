<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\LimitProfileService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LimitProfileServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new LimitProfileService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $profile = $this->validPayload();
        $reservePayload = [
            'accountReference' => 'acct-ref',
            'limitProfileCode' => 'limit-123',
            'accountName' => 'Reserved account',
            'contractCode' => 'contract-123',
        ];

        $service->all();
        $service->create($profile);
        $service->update('limit-123', $profile);
        $service->reserveAccount($reservePayload);
        $service->updateReserveAccount('acct-ref', 'limit-123');

        $this->assertRequest(1, 'GET', '/api/v1/limit-profile/');
        $this->assertRequest(2, 'POST', '/api/v1/limit-profile/');
        $this->assertSame($profile, $this->requestJson(2));
        $this->assertRequest(3, 'PUT', '/api/v1/limit-profile/limit-123');
        $this->assertRequest(4, 'POST', '/api/v1/bank-transfer/reserved-accounts/limit');
        $this->assertSame($reservePayload, $this->requestJson(4));
        $this->assertRequest(5, 'PUT', '/api/v1/bank-transfer/reserved-accounts/limit');
        $this->assertSame(['accountReference' => 'acct-ref', 'limitProfileCode' => 'limit-123'], $this->requestJson(5));
    }

    #[Test]
    public function update_requires_limit_profile_code(): void
    {
        $service = new LimitProfileService($this->apiClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit Profile Code must be provided.');

        $service->update('', $this->validPayload());
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'limitProfileName' => 'Tier 1',
            'singleTransactionValue' => 50000,
            'dailyTransactionValue' => 250000,
            'dailyTransactionVolume' => 10,
        ];
    }
}
