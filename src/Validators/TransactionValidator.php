<?php

namespace Monnify\Validators;

use InvalidArgumentException;

/**
 * @phpstan-type Payload array<string, mixed>
 */
final class TransactionValidator extends Validator
{
    /**
     * @param Payload $data
     */
    public function validateInitialize(array $data): void
    {
        $this->requireNumeric($data, 'amount');
        $this->requireMinimumAmount((float) $data['amount']);
        $this->optionalString($data, 'customerName');
        $this->requireEmail($data, 'customerEmail');
        $this->requireString($data, 'paymentReference');
        $this->optionalString($data, 'paymentDescription');
        $this->requireString($data, 'currencyCode');
        $this->requireString($data, 'contractCode');
        $this->optionalString($data, 'redirectUrl');
        $this->optionalArray($data, 'paymentMethods');
        $this->optionalArray($data, 'incomeSplitConfig');

        if (isset($data['incomeSplitConfig']) && is_array($data['incomeSplitConfig'])) {
            $splitConfig = $data['incomeSplitConfig'];
            $this->optionalString($splitConfig, 'subAccountCode');
            $this->optionalBoolean($splitConfig, 'feeBearer');
            $this->optionalNumeric($splitConfig, 'feePercentage');
            $this->optionalNumeric($splitConfig, 'splitPercentage');
            $this->optionalNumeric($splitConfig, 'splitAmount');
        }
    }

    /**
     * @param Payload $data
     */
    public function validatePayWithBankTransfer(array $data): void
    {
        $this->requireString($data, 'transactionReference');
        $this->optionalString($data, 'bankCode');
    }

    /**
     * @param Payload $data
     */
    public function validateChargeCard(array $data): void
    {
        $this->requireString($data, 'transactionReference');
        $this->requireString($data, 'collectionChannel');
        $this->requireNestedString($data, 'card', 'number');
        $this->requireNestedString($data, 'card', 'pin');
        $this->requireNestedString($data, 'card', 'expiryMonth');
        $this->requireNestedString($data, 'card', 'expiryYear');
        $this->requireNestedString($data, 'card', 'cvv');
    }

    /**
     * @param Payload $data
     */
    public function validateAuthorizeOTP(array $data): void
    {
        $this->requireString($data, 'transactionReference');
        $this->requireString($data, 'collectionChannel');
        $this->requireString($data, 'tokenId');
        $this->requireString($data, 'token');
    }

    /**
     * @param Payload $data
     */
    public function validateAuthorizeThreeDSCard(array $data): void
    {
        $this->validateChargeCard($data);
        $this->requireString($data, 'apiKey');
        $this->requireArray($data, 'deviceInformation');

        $deviceInformation = $data['deviceInformation'];
        if (! is_array($deviceInformation)) {
            throw new InvalidArgumentException('deviceInformation must be an array');
        }

        $this->requireString($deviceInformation, 'httpBrowserLanguage');
        $this->requireBoolean($deviceInformation, 'httpBrowserJavaEnabled');
        $this->requireBoolean($deviceInformation, 'httpBrowserJavaScriptEnabled');
        $this->requireString($deviceInformation, 'httpBrowserColorDepth');
        $this->requireString($deviceInformation, 'httpBrowserScreenHeight');
        $this->requireString($deviceInformation, 'httpBrowserScreenWidth');
        $this->requireString($deviceInformation, 'httpBrowserTimeDifference');
        $this->requireString($deviceInformation, 'userAgentBrowserValue');
    }

    /**
     * @param Payload $data
     */
    public function validateGetAllTransactions(array $data): void
    {
        $this->optionalInteger($data, 'page');
        $this->optionalInteger($data, 'size');
        $this->optionalString($data, 'paymentReference');
        $this->optionalString($data, 'transactionReference');
        $this->optionalNumeric($data, 'fromAmount');
        $this->optionalNumeric($data, 'toAmount');
        $this->optionalNumeric($data, 'amount');
        $this->optionalString($data, 'customerName');
        $this->optionalEmail($data, 'customerEmail');
        $this->optionalString($data, 'paymentStatus');
        $this->optionalTimestamp($data, 'from');
        $this->optionalTimestamp($data, 'to');

        if (isset($data['amount']) && is_numeric($data['amount'])) {
            $this->requireMinimumAmount((float) $data['amount']);
        }
    }

}
