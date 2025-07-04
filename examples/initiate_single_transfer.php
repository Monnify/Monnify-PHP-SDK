<?php

require_once('/src/monnify.php');
use Monnify\Monnify;  


$config = [
    'api_key' => "MF_990000",
    'secret_key' => "EN94009AL930303030",
    'contract_code' => "5120301202",
    'test' => false, // this is to 
];

$monnify = new Monnify($config);

$transferData = [
    'amount' => 200,
    'reference' => 'referen00ce---1290034',
    'narration' => '911 Transaction',
    'destinationBankCode' => '057',
    'destinationAccountNumber' => '2085886393',
    'currencyCode' => 'NGN',
    'sourceAccountNumber' => '3934178936',
];

$transferResponse = $monnify->initiateSingleTransfer($transferData);

// Handle the response as needed
