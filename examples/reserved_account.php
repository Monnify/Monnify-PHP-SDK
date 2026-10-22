<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Monnify\Monnify;
use Monnify\MonnifyConfig;

$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    contractCode: 'YOUR_CONTRACT_CODE',
));

$accountData = [
    'accountReference' => 'unique_reference',
    'accountName' => 'Account Name',
    'currencyCode' => 'NGN',
    'customerEmail' => 'customer@example.com',
    // Add other required parameters as needed
];

$reservedAccountResponse = $monnify->createReservedAccount($accountData);

// Handle the response as needed, see developers.monnify.com
