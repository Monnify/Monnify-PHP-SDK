# Monnify PHP SDK

Monnify PHP SDK is a framework-agnostic PHP wrapper for interacting with the Monnify API.

## Requirements

- PHP 8.1 or higher

## Installation

Install the SDK with Composer:

```bash
composer require monnify/monnify-php
```

## Configuration

Create a client with your Monnify API credentials:

```php
use Monnify\Monnify;
use Monnify\MonnifyConfig;

$config = MonnifyConfig::sandbox(
    apiKey: 'Your-API-Key',
    secretKey: 'Your-API-Secret',
    contractCode: 'Your-Contract-Code',
);

$monnify = new Monnify($config);
```

Use `MonnifyConfig::live()` for production:

```php
$config = MonnifyConfig::live(
    apiKey: 'Your-API-Key',
    secretKey: 'Your-API-Secret',
    contractCode: 'Your-Contract-Code',
);
```

You can override the API base URL for testing custom environments:

```php
$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'Your-API-Key',
    secretKey: 'Your-API-Secret',
    contractCode: 'Your-Contract-Code',
    apiUrl: 'https://sandbox.monnify.com',
));
```

You can also construct config from an application config array:

```php
$config = MonnifyConfig::fromArray([
    'api_key' => 'Your-API-Key',
    'secret_key' => 'Your-API-Secret',
    'contract_code' => 'Your-Contract-Code',
    'environment' => 'SANDBOX',
]);
```

## Usage

```php
$response = $monnify->initializeTransaction([
    'amount' => 5000,
    'customerName' => 'Jane Doe',
    'customerEmail' => 'jane@example.com',
    'paymentReference' => uniqid('payment-', true),
    'currencyCode' => 'NGN',
    'redirectUrl' => 'https://example.com/payment/callback',
]);
```

The configured contract code is added automatically to requests that require it when no `contractCode` is supplied in the payload.

Authentication is performed lazily on the first API call. The default in-memory token cache is scoped to the current client instance and respects Monnify's token expiry. Long-running applications can inject their own cache implementation by implementing `Monnify\Auth\TokenCacheInterface`.

## Responses And Errors

SDK methods return Monnify's decoded JSON response body as an array:

```php
$response = $monnify->getAllBanks();
```

Transport, authentication, and invalid JSON failures throw `Monnify\MonnifyException`. HTTP error exceptions include the status code, decoded response body when available, and raw response body.

## Webhooks

Verify webhook payloads with your configured secret key. The SDK expects the exact raw request body and the value of the `monnify-signature` header:

```php
use Monnify\Exceptions\InvalidWebhookSignatureException;

try {
    $monnify->webhooks()->verify($rawBody, $signature);
} catch (InvalidWebhookSignatureException $e) {
    // Reject the webhook.
}
```

You can also use the boolean helper:

```php
$isValid = $monnify->webhooks()->isValid($rawBody, $signature);
```

Webhook payloads can be wrapped for easier event handling while still preserving unknown event types:

```php
use Monnify\Enums\WebhookEventType;
use Monnify\Webhooks\WebhookPayload;

$payload = WebhookPayload::fromArray(json_decode($rawBody, true));

if ($payload->is(WebhookEventType::SuccessfulTransaction)) {
    $paymentReference = $payload->eventData['paymentReference'] ?? null;
}
```

## Documentation

For more details and API reference, please refer to the [official Monnify documentation](https://developer.monnify.com/).

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
