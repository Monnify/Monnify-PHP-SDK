<?php

namespace Monnify;

use Monnify\Auth\InMemoryTokenCache;
use Monnify\Auth\TokenCacheInterface;
use Monnify\Contracts\HttpClientInterface;
use Monnify\Http\GuzzleHttpClient;
use Monnify\Http\MonnifyApiClient;
use Monnify\Services\BillsPaymentService;
use Monnify\Services\CustomerReservedAccountService;
use Monnify\Services\DirectDebitService;
use Monnify\Services\DisbursementService;
use Monnify\Services\InvoiceService;
use Monnify\Services\LimitProfileService;
use Monnify\Services\OtherService;
use Monnify\Services\PayCodeService;
use Monnify\Services\RecurringPaymentService;
use Monnify\Services\RefundService;
use Monnify\Services\SettlementService;
use Monnify\Services\SubAccountService;
use Monnify\Services\TransactionService;
use Monnify\Services\VerificationService;
use Monnify\Services\WalletService;
use Monnify\Webhooks\WebhookSignatureVerifier;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
class Monnify
{
    private MonnifyApiClient $client;
    private ?TransactionService $transactions = null;
    private ?BillsPaymentService $billsPayments = null;
    private ?CustomerReservedAccountService $customerReservedAccounts = null;
    private ?DirectDebitService $directDebits = null;
    private ?DisbursementService $disbursements = null;
    private ?InvoiceService $invoices = null;
    private ?LimitProfileService $limitProfiles = null;
    private ?OtherService $helper = null;
    private ?PayCodeService $payCodes = null;
    private ?RecurringPaymentService $recurringPayments = null;
    private ?RefundService $refunds = null;
    private ?SettlementService $settlements = null;
    private ?SubAccountService $subAccounts = null;
    private ?VerificationService $verifications = null;
    private ?WalletService $wallets = null;
    private ?WebhookSignatureVerifier $webhooks = null;

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

    /*
     * Preferred public API.
     *
     * New SDK usage should go through these grouped services so the core and
     * Laravel packages share one service shape.
     */
    public function transactions(): TransactionService
    {
        return $this->transactions ??= new TransactionService($this->client);
    }

    public function helper(): OtherService
    {
        return $this->helper ??= new OtherService($this->client);
    }

    public function billsPayments(): BillsPaymentService
    {
        return $this->billsPayments ??= new BillsPaymentService($this->client);
    }

    public function customerReservedAccounts(): CustomerReservedAccountService
    {
        return $this->customerReservedAccounts ??= new CustomerReservedAccountService($this->client);
    }

    public function directDebits(): DirectDebitService
    {
        return $this->directDebits ??= new DirectDebitService($this->client);
    }

    public function disbursements(): DisbursementService
    {
        return $this->disbursements ??= new DisbursementService($this->client);
    }

    public function invoices(): InvoiceService
    {
        return $this->invoices ??= new InvoiceService($this->client);
    }

    public function limitProfiles(): LimitProfileService
    {
        return $this->limitProfiles ??= new LimitProfileService($this->client);
    }

    public function payCodes(): PayCodeService
    {
        return $this->payCodes ??= new PayCodeService($this->client);
    }

    public function recurringPayments(): RecurringPaymentService
    {
        return $this->recurringPayments ??= new RecurringPaymentService($this->client);
    }

    public function refunds(): RefundService
    {
        return $this->refunds ??= new RefundService($this->client);
    }

    public function settlements(): SettlementService
    {
        return $this->settlements ??= new SettlementService($this->client);
    }

    public function subAccounts(): SubAccountService
    {
        return $this->subAccounts ??= new SubAccountService($this->client);
    }

    public function verifications(): VerificationService
    {
        return $this->verifications ??= new VerificationService($this->client);
    }

    public function wallets(): WalletService
    {
        return $this->wallets ??= new WalletService($this->client);
    }

    public function webhooks(): WebhookSignatureVerifier
    {
        return $this->webhooks ??= new WebhookSignatureVerifier($this->config->secretKey);
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->transactions()->initialise($transactionData)
     *
     * @param Payload $transactionData
     * @return ResponseData
     */
    public function initializeTransaction(array $transactionData): array
    {
        return $this->transactions()->initialise($this->withContractCode($transactionData));
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->transactions()->chargeCard($payload)
     *
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
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->transactions()->status($transactionReference)
     *
     * @return ResponseData
     */
    public function getTransactionStatus(string $transactionReference): array
    {
        return $this->transactions()->status($transactionReference);
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->transactions()->all($parameters)
     *
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
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->helper()->banks()
     *
     * @return ResponseData
     */
    public function getAllBanks(): array
    {
        return $this->helper()->banks();
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->customerReservedAccounts()->createGeneralAccount($accountData)
     *
     * @param Payload $accountData
     * @return ResponseData
     */
    public function createReservedAccount(array $accountData): array
    {
        return $this->customerReservedAccounts()->createGeneralAccount($this->withContractCode($accountData));
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->customerReservedAccounts()->get($accountReference)
     *
     * @return ResponseData
     */
    public function getReservedAccountDetails(string $accountReference): array
    {
        return $this->customerReservedAccounts()->get($accountReference);
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->customerReservedAccounts()->addLinkedAccounts($accountReference, $payload)
     *
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
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->customerReservedAccounts()->transactions($accountReference, $parameters)
     *
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
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->disbursements()->singleStatus($reference)
     *
     * @return ResponseData
     */
    public function getSingleTransferStatus(string $reference): array
    {
        return $this->disbursements()->singleStatus($reference);
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->disbursements()->all('single', $pageSize, $pageNo)
     *
     * @return ResponseData
     */
    public function listAllSingleTransfers(int $pageSize, int $pageNo): array
    {
        return $this->disbursements()->all('single', $pageSize, $pageNo);
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->disbursements()->single($transferData)
     *
     * @param Payload $transferData
     * @return ResponseData
     */
    public function initiateSingleTransfer(array $transferData): array
    {
        return $this->disbursements()->single($transferData);
    }

    /**
     * Backward-compatible convenience wrapper for older core SDK usage.
     *
     * Prefer: $monnify->disbursements()->single($transferData, true)
     *
     * @param Payload $transferData
     * @return ResponseData
     */
    public function initiateAsyncTransfer(array $transferData): array
    {
        return $this->disbursements()->single($transferData, true);
    }

    /**
     * Backward-compatible boolean webhook helper for older core SDK usage.
     *
     * Prefer: $monnify->webhooks()->verify($requestBody, $receivedHash)
     * or: $monnify->webhooks()->isValid($requestBody, $receivedHash)
     */
    public function validateWebhook(string $requestBody, string $receivedHash): bool
    {
        return $this->webhooks()->isValid($requestBody, $receivedHash);
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
