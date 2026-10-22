<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Monnify\Monnify;
use Monnify\MonnifyConfig;

$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    contractCode: 'YOUR_CONTRACT_CODE',
));

$transactionData = [
    'amount' => 100.00,
    'customerName' => 'John Doe',
    'customerEmail' => 'john.doe@example.com',
    'paymentReference' => '1247xxxyyyzzz',
    'paymentDescription' => 'Trial transaction',
    'currencyCode' => 'NGN',
    'redirectUrl' => 'https://example.com/payment/callback',
    'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER'],
];

try {
    $transactionResponse = $monnify->initializeTransaction($transactionData);
    $redirectUrl = $transactionResponse['responseBody']['checkoutUrl'] ?? null;
} catch (Exception $e) {
    echo 'Transaction Error: ' . $e->getMessage();
}
