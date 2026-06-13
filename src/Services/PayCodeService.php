<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class PayCodeService
{
    public function __construct(
        private MonnifyApiClient $client,
        private SimplePayloadValidator $validator = new SimplePayloadValidator(),
    ) {
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function create(array $data): array
    {
        $this->validator->requireStrings($data, ['beneficiaryName', 'paycodeReference', 'expiryDate', 'clientId']);
        $this->validator->requireMinimum($data, 'amount', 20);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/paycode', $data);
    }

    /** @return ResponseData */
    public function get(string $payCodeReference): array
    {
        $this->requireReference($payCodeReference);

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/paycode/' . rawurlencode($payCodeReference));
    }

    /** @return ResponseData */
    public function getUnMasked(string $payCodeReference): array
    {
        $this->requireReference($payCodeReference);

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/paycode/' . rawurlencode($payCodeReference) . '/authorize');
    }

    /**
     * @param Payload $parameters
     * @return ResponseData
     */
    public function history(array $parameters): array
    {
        $this->validator->optionalStrings($parameters, ['transactionReference', 'beneficiaryName', 'transactionStatus']);
        $this->validator->optionalIntegers($parameters, ['from', 'to']);

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/paycode', query: $parameters);
    }

    /** @return ResponseData */
    public function delete(string $payCodeReference): array
    {
        $this->requireReference($payCodeReference);

        return $this->client->request(\Monnify\Enums\HttpMethod::DELETE, '/api/v1/paycode/' . rawurlencode($payCodeReference));
    }

    private function requireReference(string $payCodeReference): void
    {
        if ($payCodeReference === '') {
            throw new InvalidArgumentException('PayCode Reference must be provided.');
        }
    }
}
