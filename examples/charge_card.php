<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Monnify\Monnify;
use Monnify\MonnifyConfig;

$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    contractCode: 'YOUR_CONTRACT_CODE',
));

$transactionReference = 'MNFY|99|20220725110839|000256';
$collectionChannel = 'API_NOTIFICATION';
$cardData = [
    'number' => '4111111111111111',
    'expiryMonth' => '10',
    'expiryYear' => '2028',
    'pin' => '1234',
    'cvv' => '123',
];

$response = $monnify->chargeCard($transactionReference, $collectionChannel, $cardData);
