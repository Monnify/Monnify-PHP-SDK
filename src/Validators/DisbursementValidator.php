<?php

namespace Monnify\Validators;

use BackedEnum;
use InvalidArgumentException;

/**
 * @phpstan-type Payload array<string, mixed>
 */
final class DisbursementValidator extends Validator
{
    /**
     * @param Payload $data
     */
    public function validateSingleTransfer(array $data): void
    {
        $this->requireNumeric($data, 'amount');
        $this->requireMinimumAmount((float) $data['amount']);

        $this->requireString($data, 'reference');
        $this->requireString($data, 'narration');
        $this->requireString($data, 'destinationBankCode');
        $this->requireMaxLength($data, 'destinationBankCode', 3);
        $this->requireString($data, 'destinationAccountNumber');
        $this->requireString($data, 'destinationAccountName');
        $this->requireString($data, 'currency');
        $this->requireString($data, 'sourceAccountNumber');
    }

    /**
     * @param Payload $data
     */
    public function validateBulkTransfer(array $data): void
    {
        $this->requireString($data, 'title');
        $this->requireString($data, 'batchReference');
        $this->requireString($data, 'narration');
        $this->requireString($data, 'sourceAccountNumber');
        $this->requireIntegerLike($data, 'notificationInterval');
        $this->requireOnValidationFailure($data);
        $this->requireArray($data, 'transactionList');

        foreach ($data['transactionList'] as $transaction) {
            if (! is_array($transaction)) {
                throw new InvalidArgumentException('transactionList must be an array');
            }

            $this->requireNumeric($transaction, 'amount');
            $this->requireMinimumAmount((float) $transaction['amount']);

            $this->requireString($transaction, 'reference');
            $this->requireString($transaction, 'narration');
            $this->requireString($transaction, 'destinationBankCode');
            $this->requireMaxLength($transaction, 'destinationBankCode', 3);
            $this->requireString($transaction, 'destinationAccountNumber');
            $this->requireString($transaction, 'currency');
        }
    }

    /**
     * @param Payload $data
     */
    public function validateAuthorization(array $data): void
    {
        $this->requireString($data, 'reference');
        $this->requireString($data, 'authorizationCode');
    }

    /**
     * @param Payload $data
     */
    private function requireIntegerLike(array $data, string $key): void
    {
        if (! isset($data[$key]) || filter_var($data[$key], FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException("$key is required");
        }
    }

    /**
     * @param Payload $data
     */
    private function requireOnValidationFailure(array $data): void
    {
        if (! isset($data['onValidationFailure'])) {
            throw new InvalidArgumentException('onValidationFailure is required');
        }

        $value = $data['onValidationFailure'] instanceof BackedEnum
            ? $data['onValidationFailure']->value
            : $data['onValidationFailure'];

        if (! in_array($value, ['CONTINUE', 'BREAK'], true)) {
            throw new InvalidArgumentException('onValidationFailure is invalid');
        }
    }

    /**
     * @param Payload $data
     */
    private function requireMaxLength(array $data, string $key, int $max): void
    {
        if (isset($data[$key]) && is_string($data[$key]) && strlen($data[$key]) > $max) {
            throw new InvalidArgumentException("$key must not be greater than $max characters");
        }
    }
}
