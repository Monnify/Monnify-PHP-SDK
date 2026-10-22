<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class VerificationService
{
    public function __construct(
        private MonnifyApiClient $client,
        private SimplePayloadValidator $validator = new SimplePayloadValidator(),
    ) {
    }

    /** @return ResponseData */
    public function bankAccount(string $accountNumber, string $bankCode): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/disbursements/account/validate', query: [
            'accountNumber' => $accountNumber,
            'bankCode' => $bankCode,
        ]);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function bvnInformation(array $data): array
    {
        $this->validator->requireStrings($data, ['bvn', 'name', 'dateOfBirth', 'mobileNo']);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/vas/bvn-details-match', $data);
    }

    /** @return ResponseData */
    public function matchBVNAndBankAccount(string $bvn, string $bankCode, string $accountNumber): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/vas/bvn-account-match', [
            'bvn' => $bvn,
            'bankCode' => $bankCode,
            'accountNumber' => $accountNumber,
        ]);
    }

    /** @return ResponseData */
    public function nin(string $nin): array
    {
        if ($nin === '') {
            throw new InvalidArgumentException('NIN must be provided.');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/vas/nin-details', ['nin' => $nin]);
    }
}
