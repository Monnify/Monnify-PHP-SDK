<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Monnify\Monnify;
use Monnify\MonnifyConfig;

$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    contractCode: 'YOUR_CONTRACT_CODE',
));

$transferData = [
    'amount' => 200,
    'reference' => 'reference-1290034',
    'narration' => '911 Transaction',
    'destinationBankCode' => '057',
    'destinationAccountNumber' => '2085886393',
    'currencyCode' => 'NGN',
    'sourceAccountNumber' => '3934178936',
];

$transferResponse = $monnify->initiateSingleTransfer($transferData);

// Handle the response as needed
