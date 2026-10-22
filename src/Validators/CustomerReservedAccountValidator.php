<?php

namespace Monnify\Validators;

use InvalidArgumentException;

/**
 * @phpstan-type Payload array<string, mixed>
 */
final class CustomerReservedAccountValidator extends Validator
{
    /**
     * @param Payload $data
     */
    public function validateCreateGeneralAccount(array $data): void
    {
        $this->requireString($data, 'accountReference');
        $this->requireString($data, 'accountName');
        $this->requireString($data, 'currencyCode');
        $this->requireString($data, 'contractCode');
        $this->requireEmail($data, 'customerEmail');
        $this->optionalString($data, 'customerName');
        $this->requireBoolean($data, 'getAllAvailableBanks');
        $this->optionalBoolean($data, 'restrictPaymentSource');
        $this->optionalArray($data, 'incomeSplitConfig');
        if (! array_key_exists('allowedPaymentSource', $data) || $data['allowedPaymentSource'] !== null) {
            $this->optionalArray($data, 'allowedPaymentSource');
        }

        if (! isset($data['bvn']) && ! isset($data['nin'])) {
            throw new InvalidArgumentException('bvn or nin is required');
        }
    }

    /**
     * @param Payload $data
     */
    public function validateCreateInvoiceAccount(array $data): void
    {
        $this->requireString($data, 'contractCode');
        $this->requireString($data, 'accountName');
        $this->requireString($data, 'currencyCode');
        $this->requireString($data, 'accountReference');
        $this->optionalString($data, 'customerName');
        $this->requireEmail($data, 'customerEmail');
        $this->optionalString($data, 'reservedAccountType');
    }

    /**
     * @param Payload $data
     */
    public function validateAddLinkedAccounts(array $data): void
    {
        $this->optionalBoolean($data, 'getAllAvailableBanks');
        $this->optionalArray($data, 'preferredBanks');
    }

    /**
     * @param Payload $data
     */
    public function validateAllowedPaymentSource(array $data): void
    {
        $this->optionalBoolean($data, 'restrictPaymentSource');

        if (array_key_exists('allowedPaymentSource', $data) && $data['allowedPaymentSource'] === null) {
            return;
        }

        if (isset($data['allowedPaymentSource']) && ! is_array($data['allowedPaymentSource'])) {
            throw new InvalidArgumentException('allowedPaymentSource must be an array');
        }

        if (isset($data['allowedPaymentSource'])) {
            $this->optionalArray($data['allowedPaymentSource'], 'bvns');
        }
    }

    /**
     * @param Payload $data
     */
    public function validateUpdateSplitConfig(array $data): void
    {
        if ($data === []) {
            throw new InvalidArgumentException('splits must be an array');
        }

        foreach ($data as $split) {
            if (! is_array($split)) {
                throw new InvalidArgumentException('splits must be an array');
            }

            $this->optionalString($split, 'subAccountCode');
            $this->optionalBoolean($split, 'feeBearer');
            $this->optionalNonNegativeNumeric($split, 'feePercentage');
            $this->optionalNonNegativeNumeric($split, 'splitPercentage');
        }
    }

    /**
     * @param Payload $data
     */
    public function validateGetReservedAccountTransactions(array $data): void
    {
        $this->optionalInteger($data, 'page');
        $this->optionalInteger($data, 'size');
    }

    /**
     * @param Payload $data
     */
    public function validateUpdateKYCInfo(array $data): void
    {
        $this->optionalString($data, 'bvn');
        $this->optionalString($data, 'nin');
    }

    /**
     * @param Payload $data
     */
    private function optionalNonNegativeNumeric(array $data, string $key): void
    {
        if (! isset($data[$key])) {
            return;
        }

        if (! is_numeric($data[$key]) || (float) $data[$key] < 0) {
            throw new InvalidArgumentException("$key must be numeric and at least 0");
        }
    }
}
