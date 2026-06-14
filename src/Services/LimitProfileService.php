<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class LimitProfileService
{
    public function __construct(
        private MonnifyApiClient $client,
        private SimplePayloadValidator $validator = new SimplePayloadValidator(),
    ) {
    }

    /** @return ResponseData */
    public function all(): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/limit-profile/');
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function create(array $data): array
    {
        $this->validateLimitProfile($data);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/limit-profile/', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function update(string $limitProfileCode, array $data): array
    {
        if ($limitProfileCode === '') {
            throw new InvalidArgumentException('Limit Profile Code must be provided.');
        }

        $this->validateLimitProfile($data);

        return $this->client->request(\Monnify\Enums\HttpMethod::PUT, '/api/v1/limit-profile/' . rawurlencode($limitProfileCode), $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function reserveAccount(array $data): array
    {
        $this->validator->requireStrings($data, ['accountReference', 'limitProfileCode', 'accountName', 'contractCode']);
        $this->validator->optionalStrings($data, ['currencyCode', 'customerEmail']);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/bank-transfer/reserved-accounts/limit', $data);
    }

    /** @return ResponseData */
    public function updateReserveAccount(string $accountReference, string $limitProfileCode): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::PUT, '/api/v1/bank-transfer/reserved-accounts/limit', [
            'accountReference' => $accountReference,
            'limitProfileCode' => $limitProfileCode,
        ]);
    }

    /** @param Payload $data */
    private function validateLimitProfile(array $data): void
    {
        $this->validator->requireString($data, 'limitProfileName');
        $this->validator->requireNumerics($data, [
            'singleTransactionValue',
            'dailyTransactionValue',
            'dailyTransactionVolume',
        ]);
    }
}
