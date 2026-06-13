<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;

/**
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class SettlementService
{
    public function __construct(private MonnifyApiClient $client)
    {
    }

    /** @return ResponseData */
    public function transactions(string $settlementReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/transactions/find-by-settlement-reference', query: [
            'reference' => $settlementReference,
            'size' => $pageSize,
            'page' => $pageNumber,
        ]);
    }

    /** @return ResponseData */
    public function getByTransaction(string $transactionReference): array
    {
        if ($transactionReference === '') {
            throw new InvalidArgumentException('Transaction Reference must be provided.');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/settlement-detail', query: ['transactionReference' => $transactionReference]);
    }
}
