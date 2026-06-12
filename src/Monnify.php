<?php

namespace Monnify;

use Monnify\Auth\InMemoryTokenCache;
use Monnify\Auth\TokenCacheInterface;
use Monnify\Contracts\HttpClientInterface;
use Monnify\Http\GuzzleHttpClient;
use Monnify\Http\MonnifyApiClient;
use Monnify\Services\CustomerReservedAccountService;
use Monnify\Services\OtherService;
use Monnify\Services\TransactionService;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
class Monnify
{
    private MonnifyApiClient $client;
    private ?TransactionService $transactions = null;
    private ?CustomerReservedAccountService $customerReservedAccounts = null;
    private ?OtherService $helper = null;

    public function __construct(
        private MonnifyConfig $config,
        ?HttpClientInterface $client = null,
        ?TokenCacheInterface $tokenCache = null,
    ) {
        $this->client = new MonnifyApiClient(
            config: $this->config,
            client: $client ?? new GuzzleHttpClient($this->config->baseUrl()),
            tokenCache: $tokenCache ?? new InMemoryTokenCache(),
        );
    }

    public function transactions(): TransactionService
    {
        return $this->transactions ??= new TransactionService($this->client);
    }

    public function helper(): OtherService
    {
        return $this->helper ??= new OtherService($this->client);
    }

    public function customerReservedAccounts(): CustomerReservedAccountService
    {
        return $this->customerReservedAccounts ??= new CustomerReservedAccountService($this->client);
    }

    /**
     * @param Payload $transactionData
     * @return ResponseData
     */
    public function initializeTransaction(array $transactionData): array
    {
        return $this->transactions()->initialise($this->withContractCode($transactionData));
    }

    /**
     * @param Payload $cardData
     * @return ResponseData
     */
    public function chargeCard(string $transactionReference, string $collectionChannel, array $cardData): array
    {
        return $this->transactions()->chargeCard([
            'transactionReference' => $transactionReference,
            'collectionChannel' => $collectionChannel,
            'card' => $cardData,
        ]);
    }

    /**
     * @return ResponseData
     */
    public function getTransactionStatus(string $transactionReference): array
    {
        return $this->transactions()->status($transactionReference);
    }

    /**
     * @param Payload $filters
     * @return ResponseData
     */
    public function getAllTransactions(int $page = 0, int $size = 10, array $filters = []): array
    {
        return $this->transactions()->all(array_merge([
            'page' => $page,
            'size' => $size,
        ], $filters));
    }

    /**
     * @return ResponseData
     */
    public function getAllBanks(): array
    {
        return $this->helper()->banks();
    }

    /**
     * @param Payload $accountData
     * @return ResponseData
     */
    public function createReservedAccount(array $accountData): array
    {
        return $this->customerReservedAccounts()->createGeneralAccount($this->withContractCode($accountData));
    }

    /**
     * @return ResponseData
     */
    public function getReservedAccountDetails(string $accountReference): array
    {
        return $this->customerReservedAccounts()->get($accountReference);
    }

    /**
     * @param list<string> $preferredBanks
     * @return ResponseData
     */
    public function addLinkedAccounts(string $accountReference, bool $getAllAvailableBanks, array $preferredBanks = []): array
    {
        return $this->customerReservedAccounts()->addLinkedAccounts($accountReference, [
            'getAllAvailableBanks' => $getAllAvailableBanks,
            'preferredBanks' => $preferredBanks,
        ]);
    }

    /**
     * @return ResponseData
     */
    public function getReservedAccountTransactions(string $accountReference, int $page = 0, int $size = 10): array
    {
        return $this->customerReservedAccounts()->transactions($accountReference, [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * @return ResponseData
     */
    public function getSingleTransferStatus(string $reference): array
    {
        return $this->client->request('GET', '/api/v2/disbursements/single/summary', [], [
            'reference' => $reference,
        ]);
    }

    /**
     * @return ResponseData
     */
    public function listAllSingleTransfers(int $pageSize, int $pageNo): array
    {
        return $this->client->request('GET', '/api/v2/disbursements/single/transactions', [], [
            'pageSize' => $pageSize,
            'pageNo' => $pageNo,
        ]);
    }

    /**
     * @param Payload $transferData
     * @return ResponseData
     */
    public function initiateSingleTransfer(array $transferData): array
    {
        return $this->client->request('POST', '/api/v2/disbursements/single', $transferData);
    }

    /**
     * @param Payload $transferData
     * @return ResponseData
     */
    public function initiateAsyncTransfer(array $transferData): array
    {
        return $this->client->request('POST', '/api/v2/disbursements/single', $transferData);
    }

    public function validateWebhook(string $requestBody, string $receivedHash): bool
    {
        return hash_equals(hash_hmac('sha512', $requestBody, $this->config->secretKey), $receivedHash);
    }

    /**
     * @param Payload $data
     * @return Payload
     */
    private function withContractCode(array $data): array
    {
        return $data + ['contractCode' => $this->config->contractCode];
    }
}
