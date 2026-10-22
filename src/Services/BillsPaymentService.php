<?php

namespace Monnify\Services;

use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class BillsPaymentService
{
    public function __construct(
        private MonnifyApiClient $client,
        private SimplePayloadValidator $validator = new SimplePayloadValidator(),
    ) {
    }

    /** @return ResponseData */
    public function categories(int $pageSize = 10, int $pageNumber = 0): array
    {
        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/vas/bills-payment/biller-categories', query: [
            'size' => $pageSize,
            'page' => $pageNumber,
        ]);
    }

    /** @return ResponseData */
    public function billers(string $categoryCode = '', int $pageSize = 10, int $pageNumber = 0): array
    {
        $parameters = [
            'size' => $pageSize,
            'page' => $pageNumber,
        ];

        if ($categoryCode !== '') {
            $parameters['category_code'] = $categoryCode;
        }

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/vas/bills-payment/billers', query: $parameters);
    }

    /** @return ResponseData */
    public function products(string $billerCode, int $pageSize = 10, int $pageNumber = 0): array
    {
        $this->validator->requireString(['billerCode' => $billerCode], 'billerCode');

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/vas/bills-payment/biller-products', query: [
            'biller_code' => $billerCode,
            'size' => $pageSize,
            'page' => $pageNumber,
        ]);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function validateCustomer(array $data): array
    {
        $this->validator->requireStrings($data, ['productCode', 'customerId']);

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/vas/bills-payment/validate-customer', $data);
    }

    /**
     * @param Payload $data
     * @return ResponseData
     */
    public function vend(array $data): array
    {
        $this->validator->requireStrings($data, ['productCode', 'customerId', 'vendReference']);
        $this->validator->requireMinimum($data, 'vendAmount', 0.01);
        $this->validator->optionalString($data, 'validationReference');

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/vas/bills-payment/vend', $data);
    }

    /** @return ResponseData */
    public function requery(string $vendReference): array
    {
        $this->validator->requireString(['vendReference' => $vendReference], 'vendReference');

        return $this->client->request(\Monnify\Enums\HttpMethod::GET, '/api/v1/vas/bills-payment/requery', query: ['vendReference' => $vendReference]);
    }
}
