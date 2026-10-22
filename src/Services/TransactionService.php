<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Enums\HttpMethod;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\TransactionValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class TransactionService
{
    public function __construct(
        private MonnifyApiClient $client,
        private TransactionValidator $validator = new TransactionValidator(),
    ) {
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function initialise(array $data): array
    {
        $data = $this->withDefaultContractCode($data);

        $this->validator->validateInitialize($data);

        return $this->client->request(HttpMethod::POST, '/api/v1/merchant/transactions/init-transaction', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function payWithBankTransfer(array $data): array
    {
        $this->validator->validatePayWithBankTransfer($data);

        return $this->client->request(HttpMethod::POST, '/api/v1/merchant/bank-transfer/init-payment', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function chargeCard(array $data): array
    {
        $this->validator->validateChargeCard($data);

        return $this->client->request(HttpMethod::POST, '/api/v1/merchant/cards/charge', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function authorizeOTP(array $data): array
    {
        $this->validator->validateAuthorizeOTP($data);

        return $this->client->request(HttpMethod::POST, '/api/v1/merchant/cards/otp/authorize', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function authorizeThreeDSCard(array $data): array
    {
        $this->validator->validateAuthorizeThreeDSCard($data);

        return $this->client->request(HttpMethod::POST, '/api/v1/sdk/cards/secure-3d/authorize', $data);
    }

    /**
     * @param Payload $parameters
     * @return ResponseData
     */
    public function all(array $parameters = []): array
    {
        $this->validator->validateGetAllTransactions($parameters);

        return $this->client->request(HttpMethod::GET, '/api/v1/transactions/search', query: $parameters);
    }

    /**
     * @return ResponseData
     */
    public function status(string $transactionReference): array
    {
        if ($transactionReference === '') {
            throw new InvalidArgumentException('Transaction Reference must be provided');
        }

        return $this->client->request(HttpMethod::GET, '/api/v2/transactions/' . rawurlencode($transactionReference));
    }

    /**
     * @return ResponseData
     */
    public function statusByReference(string $reference, string $referenceType = 'transaction'): array
    {
        if ($reference === '') {
            throw new InvalidArgumentException('Reference must be provided');
        }

        if ($referenceType !== 'transaction' && $referenceType !== 'payment') {
            throw new InvalidArgumentException('Either transaction or payment must be provided as referenceType');
        }

        $paramKey = $referenceType === 'transaction' ? 'transactionReference' : 'paymentReference';

        return $this->client->request(HttpMethod::GET, '/api/v2/merchant/transactions/query', query: [
            $paramKey => $reference,
        ]);
    }

    /**
     * @param Payload $data
     * @return Payload
     */
    private function withDefaultContractCode(array $data): array
    {
        return $data + ['contractCode' => $this->client->config()->contractCode];
    }
}
