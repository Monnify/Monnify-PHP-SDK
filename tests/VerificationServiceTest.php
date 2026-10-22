<?php

namespace Monnify\Tests;

use InvalidArgumentException;
use Monnify\Services\VerificationService;
use Monnify\Tests\Concerns\CreatesMockApiClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerificationServiceTest extends TestCase
{
    use CreatesMockApiClient;

    #[Test]
    public function it_uses_expected_endpoints(): void
    {
        $service = new VerificationService($this->apiClient([
            $this->authResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
            $this->okResponse(),
        ]));
        $bvnPayload = [
            'bvn' => '12345678901',
            'name' => 'Jane Doe',
            'dateOfBirth' => '1990-01-01',
            'mobileNo' => '08012345678',
        ];

        $service->bankAccount('0123456789', '058');
        $service->bvnInformation($bvnPayload);
        $service->matchBVNAndBankAccount('12345678901', '058', '0123456789');
        $service->nin('12345678901');

        $this->assertRequest(1, 'GET', '/api/v1/disbursements/account/validate', 'accountNumber=0123456789&bankCode=058');
        $this->assertRequest(2, 'POST', '/api/v1/vas/bvn-details-match');
        $this->assertSame($bvnPayload, $this->requestJson(2));
        $this->assertRequest(3, 'POST', '/api/v1/vas/bvn-account-match');
        $this->assertSame([
            'bvn' => '12345678901',
            'bankCode' => '058',
            'accountNumber' => '0123456789',
        ], $this->requestJson(3));
        $this->assertRequest(4, 'POST', '/api/v1/vas/nin-details');
        $this->assertSame(['nin' => '12345678901'], $this->requestJson(4));
    }

    #[Test]
    public function bvn_information_requires_bvn(): void
    {
        $service = new VerificationService($this->apiClient([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('bvn is required');

        $service->bvnInformation([]);
    }
}
