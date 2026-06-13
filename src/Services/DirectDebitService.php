<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class DirectDebitService
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
        $this->validator->requireStrings($data, [
            'contractCode',
            'mandateReference',
            'customerName',
            'customerPhoneNumber',
            'customerAddress',
            'customerAccountNumber',
            'customerAccountBankCode',
            'mandateDescription',
            'mandateStartDate',
            'mandateEndDate',
        ]);
        $this->validator->requireEmailField($data, 'customerEmailAddress');
        $this->validator->optionalBooleans($data, ['autoRenew', 'customerCancellation']);
        $this->validator->optionalMinimum($data, 'mandateAmount', 20);
        $this->validator->optionalMinimum($data, 'debitAmount', 20);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/direct-debit/mandate/create', $data);
    }

    /** @return ResponseData */
    public function get(string $mandateReference): array
    {
        if ($mandateReference === '') {
            throw new InvalidArgumentException('Mandate Reference must be provided.');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/direct-debit/mandate/', query: ['mandateReferences' => $mandateReference]);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function debit(array $data): array
    {
        $this->validator->requireStrings($data, ['paymentReference', 'mandateCode', 'narration']);
        $this->validator->requireEmailField($data, 'customerEmail');
        $this->validator->optionalMinimum($data, 'debitAmount', 20);
        $this->validator->optionalArray($data, 'incomeSplit');
        $this->validator->optionalArray($data, 'incomeSplitConfig');

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/direct-debit/mandate/debit', $data);
    }

    /** @return ResponseData */
    public function status(string $paymentReference): array
    {
        if ($paymentReference === '') {
            throw new InvalidArgumentException('Payment Reference must be provided.');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/direct-debit/mandate/debit-status', query: ['paymentReference' => $paymentReference]);
    }

    /** @return ResponseData */
    public function cancel(string $mandateCode): array
    {
        if ($mandateCode === '') {
            throw new InvalidArgumentException('Mandate Code must be provided.');
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::PATCH, '/api/v1/direct-debit/mandate/cancel-mandate/' . rawurlencode($mandateCode));
    }
}
