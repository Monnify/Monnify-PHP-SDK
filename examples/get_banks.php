<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Monnify\Monnify;
use Monnify\MonnifyConfig;

$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    contractCode: 'YOUR_CONTRACT_CODE',
));

$banksResponse = $monnify->getAllBanks();
