<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class SubAccountService
{
    public function __construct(
        private MonnifyApiClient $client,
        private SimplePayloadValidator $validator = new SimplePayloadValidator(),
    ) {
    }

    /**
     * @param list<Payload> $data
     * @return ResponseData
     */
    public function create(array $data): array
    {
        foreach ($data as $account) {
            $this->validateAccount($account);
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/sub-accounts', $data);
    }

    /** @return ResponseData */
    public function all(): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/sub-accounts');
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function update(array $data): array
    {
        $this->validateAccount($data);

        return $this->client->request(\Monnify\Enums\HttpMethod::PUT, '/api/v1/sub-accounts', $data);
    }

    /** @return ResponseData */
    public function delete(string $subAccountCode): array
    {
        if ($subAccountCode === '') {
            throw new InvalidArgumentException('Sub Account Code must be provided');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::DELETE, '/api/v1/sub-accounts/' . rawurlencode($subAccountCode));
    }

    /** @param Payload $data */
    private function validateAccount(array $data): void
    {
        $this->validator->requireStrings($data, ['currencyCode', 'accountNumber', 'bankCode', 'email']);
        $this->validator->optionalString($data, 'subAccountCode');
        $this->validator->requireMinimum($data, 'defaultSplitPercentage', 20);
    }
}
