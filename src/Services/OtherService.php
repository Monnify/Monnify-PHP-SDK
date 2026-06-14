<?php

namespace Monnify\Services;

use Monnify\Enums\HttpMethod;
use Monnify\Http\MonnifyApiClient;

/**
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class OtherService
{
    public function __construct(private MonnifyApiClient $client)
    {
    }

    /**
     * @return ResponseData
     */
    public function banks(): array
    {
        return $this->client->request(HttpMethod::GET, '/api/v1/banks');
    }

    /**
     * @return ResponseData
     */
    public function banksWithUSSD(): array
    {
        return $this->client->request(HttpMethod::GET, '/api/v1/sdk/transactions/banks');
    }
}
