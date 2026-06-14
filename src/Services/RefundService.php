<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class RefundService
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
    public function initialise(array $data): array
    {
        $this->validator->requireStrings($data, ['transactionReference', 'refundReference', 'refundReason', 'customerNote']);
        $this->validator->requireNumeric($data, 'refundAmount');
        $this->validator->optionalStrings($data, ['destinationAccountNumber', 'destinationAccountBankCode']);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/refunds/initiate-refund', $data);
    }

    /** @return ResponseData */
    public function all(int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/refunds', query: ['size' => $pageSize, 'page' => $pageNumber]);
    }

    /** @return ResponseData */
    public function status(string $refundReference): array
    {
        if ($refundReference === '') {
            throw new InvalidArgumentException('Refund Reference must be provided.');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/refunds/' . rawurlencode($refundReference));
    }
}
