<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Enums\HttpMethod;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\CustomerReservedAccountValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class CustomerReservedAccountService
{
    public function __construct(
        private MonnifyApiClient $client,
        private CustomerReservedAccountValidator $validator = new CustomerReservedAccountValidator(),
    ) {
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function createGeneralAccount(array $data): array
    {
        $data = $this->withDefaultContractCode($data);
        $this->validator->validateCreateGeneralAccount($data);

        return $this->client->request(HttpMethod::POST, '/api/v2/bank-transfer/reserved-accounts', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function createInvoiceAccount(array $data): array
    {
        $data = $this->withDefaultContractCode($data);
        $this->validator->validateCreateInvoiceAccount($data);

        return $this->client->request(HttpMethod::POST, '/api/v1/bank-transfer/reserved-accounts', $data);
    }

    /**
     * @return ResponseData
     */
    public function get(string $accountReference): array
    {
        $this->requireAccountReference($accountReference);

        return $this->client->request(HttpMethod::GET, '/api/v2/bank-transfer/reserved-accounts/' . rawurlencode($accountReference));
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function addLinkedAccounts(string $accountReference, array $data = []): array
    {
        $this->requireAccountReference($accountReference);
        $this->validator->validateAddLinkedAccounts($data);

        return $this->client->request(HttpMethod::PUT, '/api/v1/bank-transfer/reserved-accounts/add-linked-accounts/' . rawurlencode($accountReference), $data);
    }

    /**
     * @return ResponseData
     */
    public function updateBVN(string $accountReference, string $bvn): array
    {
        $this->requireAccountReference($accountReference);

        return $this->client->request(HttpMethod::PUT, '/api/v1/bank-transfer/reserved-accounts/update-customer-bvn/' . rawurlencode($accountReference), [
            'bvn' => $bvn,
        ]);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function allowedPaymentSource(string $accountReference, array $data): array
    {
        $this->requireAccountReference($accountReference);
        $this->validator->validateAllowedPaymentSource($data);

        return $this->client->request(HttpMethod::PUT, '/api/v1/bank-transfer/reserved-accounts/update-payment-source-filter/' . rawurlencode($accountReference), $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function updateSplitConfig(string $accountReference, array $data): array
    {
        $this->requireAccountReference($accountReference);
        $this->validator->validateUpdateSplitConfig($data);

        return $this->client->request(HttpMethod::PUT, '/api/v1/bank-transfer/reserved-accounts/update-income-split-config/' . rawurlencode($accountReference), $data);
    }

    /**
     * @return ResponseData
     */
    public function deallocateAccount(string $accountReference): array
    {
        $this->requireAccountReference($accountReference);

        return $this->client->request(HttpMethod::DELETE, '/api/v1/bank-transfer/reserved-accounts/reference/' . rawurlencode($accountReference));
    }

    /**
     * @param Payload $parameters
     * @return ResponseData
     */
    public function transactions(string $accountReference, array $parameters = []): array
    {
        $this->requireAccountReference($accountReference);
        $this->validator->validateGetReservedAccountTransactions($parameters);

        return $this->client->request(HttpMethod::GET, '/api/v1/bank-transfer/reserved-accounts/transactions', query: ['accountReference' => $accountReference] + $parameters);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function updateKYCInfo(string $accountReference, array $data): array
    {
        $this->requireAccountReference($accountReference);
        $this->validator->validateUpdateKYCInfo($data);

        return $this->client->request(HttpMethod::PUT, '/api/v1/bank-transfer/reserved-accounts/' . rawurlencode($accountReference) . '/kyc-info', $data);
    }

    private function requireAccountReference(string $accountReference): void
    {
        if ($accountReference === '') {
            throw new InvalidArgumentException('Account Reference must be provided');
        }
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
