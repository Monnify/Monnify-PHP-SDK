<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class WalletService
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
        $this->validator->optionalStrings($data, ['walletReference', 'walletName', 'customerName']);
        if (isset($data['customerEmail'])) {
            $this->validator->requireEmailField($data, 'customerEmail');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/disbursements/wallet', $data);
    }

    /** @return ResponseData */
    public function get(string $customerEmail, int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/disbursements/wallet', query: [
            'customerEmail' => $customerEmail,
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber,
        ]);
    }

    /** @return ResponseData */
    public function balance(string $accountNumber): array
    {
        $this->requireAccountNumber($accountNumber);

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/disbursements/wallet/balance', query: ['accountNumber' => $accountNumber]);
    }

    /** @return ResponseData */
    public function transactions(string $accountNumber, int $pageSize = 10, int $pageNumber = 0): array
    {
        $this->requireAccountNumber($accountNumber);

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/disbursements/wallet/transactions', query: [
            'accountNumber' => $accountNumber,
            'pageSize' => $pageSize,
            'pageNo' => $pageNumber,
        ]);
    }

    private function requireAccountNumber(string $accountNumber): void
    {
        if ($accountNumber === '') {
            throw new InvalidArgumentException('Account Number must provided.');
        }
    }
}
