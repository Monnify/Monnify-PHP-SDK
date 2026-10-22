<?php

namespace Monnify\Services;

use InvalidArgumentException;
use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class InvoiceService
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
        $this->validateInvoice($data);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/invoice/create', $data);
    }

    /** @return ResponseData */
    public function get(string $invoiceReference): array
    {
        $this->requireReference($invoiceReference);

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/invoice/' . rawurlencode($invoiceReference) . '/details');
    }

    /** @return ResponseData */
    public function all(): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/invoice/all');
    }

    /** @return ResponseData */
    public function cancel(string $invoiceReference): array
    {
        $this->requireReference($invoiceReference);

        return $this->client->request(\Monnify\Enums\HttpMethod::DELETE, '/api/v1/invoice/' . rawurlencode($invoiceReference) . '/cancel');
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function attachReservedAccount(array $data): array
    {
        return $this->create($data);
    }

    /** @param Payload $data */
    private function validateInvoice(array $data): void
    {
        $this->validator->requireMinimum($data, 'amount', 20);
        $this->validator->requireStrings($data, [
            'currencyCode',
            'invoiceReference',
            'customerName',
            'contractCode',
            'description',
            'expiryDate',
        ]);
        $this->validator->requireEmailField($data, 'customerEmail');
        $this->validator->optionalString($data, 'accountReference');
        if (! array_key_exists('incomeSplitConfig', $data) || $data['incomeSplitConfig'] !== null) {
            $this->validator->optionalArray($data, 'incomeSplitConfig');
        }

        if (! array_key_exists('redirectUrl', $data) || $data['redirectUrl'] !== null) {
            $this->validator->optionalString($data, 'redirectUrl');
        }
    }

    private function requireReference(string $invoiceReference): void
    {
        if ($invoiceReference === '') {
            throw new InvalidArgumentException('Invoice Reference must be provided.');
        }
    }
}
