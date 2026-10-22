<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Enums\HttpMethod;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\DisbursementValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class DisbursementService
{
    public function __construct(
        private MonnifyApiClient $client,
        private DisbursementValidator $validator = new DisbursementValidator(),
    ) {
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function single(array $data, bool $asynchronous = false): array
    {
        $this->validator->validateSingleTransfer($data);

        if ($asynchronous) {
            $data['async'] = true;
        }

        return $this->client->request(HttpMethod::POST, '/api/v2/disbursements/single', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function bulk(array $data): array
    {
        $this->validator->validateBulkTransfer($data);

        return $this->client->request(HttpMethod::POST, '/api/v2/disbursements/batch', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function authoriseSingle(array $data): array
    {
        $this->validator->validateAuthorization($data);

        return $this->client->request(HttpMethod::POST, '/api/v2/disbursements/single/validate-otp', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function authoriseBulk(array $data): array
    {
        $this->validator->validateAuthorization($data);

        return $this->client->request(HttpMethod::POST, '/api/v2/disbursements/batch/validate-otp', $data);
    }

    /**
     * @return ResponseData
     */
    public function resendOTP(string $reference): array
    {
        $this->requireReference($reference);

        return $this->client->request(HttpMethod::POST, '/api/v2/disbursements/single/resend-otp', ['reference' => $reference]);
    }

    /**
     * @return ResponseData
     */
    public function bulkResendOTP(string $reference): array
    {
        $this->requireReference($reference);

        return $this->client->request(HttpMethod::POST, '/api/v2/disbursements/batch/resend-otp', ['reference' => $reference]);
    }

    /**
     * @return ResponseData
     */
    public function bulkBatchSummary(string $batchReference): array
    {
        if ($batchReference === '') {
            throw new InvalidArgumentException('Batch Reference must be provided');
        }

        return $this->client->request(HttpMethod::GET, '/api/v2/disbursements/batch/summary', query: ['reference' => $batchReference]);
    }

    /**
     * @return ResponseData
     */
    public function singleStatus(string $reference): array
    {
        return $this->client->request(HttpMethod::GET, '/api/v2/disbursements/single/summary', query: ['reference' => $reference]);
    }

    /**
     * @return ResponseData
     */
    public function bulkStatus(string $batchReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->client->request(HttpMethod::GET, '/api/v2/disbursements/bulk/' . rawurlencode($batchReference) . '/transactions', query: [
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber,
        ]);
    }

    /**
     * @return ResponseData
     */
    public function all(string $type = 'single', int $pageSize = 10, int $pageNumber = 0): array
    {
        if (! in_array($type, ['single', 'bulk'], true)) {
            throw new InvalidArgumentException("Type must be 'single' or 'bulk'.");
        }

        $endpoint = $type === 'single'
            ? '/api/v2/disbursements/single/transactions'
            : '/api/v2/disbursements/bulk/transactions';

        return $this->client->request(HttpMethod::GET, $endpoint, query: [
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber,
        ]);
    }

    /**
     * @return ResponseData
     */
    public function bulkTransaction(string $batchReference, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->bulkStatus($batchReference, $pageSize, $pageNumber);
    }

    /**
     * @return ResponseData
     */
    public function search(string $sourceAccountNumber, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->client->request(HttpMethod::GET, '/api/v2/disbursements/search-transactions', query: [
            'sourceAccountNumber' => $sourceAccountNumber,
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber,
        ]);
    }

    /**
     * @return ResponseData
     */
    public function walletBalance(string $accountNumber): array
    {
        if ($accountNumber === '') {
            throw new InvalidArgumentException('Account Number must be provided.');
        }

        return $this->client->request(HttpMethod::GET, '/api/v2/disbursements/wallet-balance', query: ['accountNumber' => $accountNumber]);
    }

    private function requireReference(string $reference): void
    {
        if ($reference === '') {
            throw new InvalidArgumentException('Reference must be provided');
        }
    }
}
