<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Monnify\Monnify;
use Monnify\MonnifyConfig;

$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    contractCode: 'YOUR_CONTRACT_CODE',
));

// Assuming you have received a webhook request
$requestBody = file_get_contents('php://input') ?: ''; // Get the request body

// Extract the received hash from the request headers
$receivedHash = $_SERVER['HTTP_X_MONNIFY_SIGNATURE'] ?? '';

// Validate the webhook
if ($monnify->validateWebhook($requestBody, $receivedHash)) {
    // repond to monnify before you continue to process your data
    // Webhook is valid, you can process the data
    // Your webhook processing code here
    http_response_code(200);
} else {
    // Webhook is not valid, handle it as needed (e.g., log or reject)
    // Your handling code here
    http_response_code(401);
}
