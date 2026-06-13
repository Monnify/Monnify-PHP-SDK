<?php

namespace Monnify\Services;

use Monnify\Http\MonnifyApiClient;
use Monnify\Validators\SimplePayloadValidator;

/**
 * @phpstan-type Payload array<string, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class RecurringPaymentService
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
    public function chargeCardToken(array $data): array
    {
        $this->validator->requireMinimum($data, 'amount', 20);
        $this->validator->requireStrings($data, ['cardToken', 'paymentReference', 'contractCode', 'apiKey']);
        $this->validator->requireEmailField($data, 'customerEmail');
        $this->validator->optionalStrings($data, ['customerName', 'paymentDescription', 'currencyCode']);
        $this->validator->optionalArray($data, 'incomeSplitConfig');
        $this->validator->optionalString(isset($data['metaData']) && is_array($data['metaData']) ? $data['metaData'] : [], 'ipAddress');
        $this->validator->optionalString(isset($data['metaData']) && is_array($data['metaData']) ? $data['metaData'] : [], 'deviceType');

        return $this->client->request(\Monnify\Enums\HttpMethod::POST, '/api/v1/merchant/cards/charge-card-token', $data);
    }
}
