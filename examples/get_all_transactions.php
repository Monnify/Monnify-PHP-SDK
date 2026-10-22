<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Monnify\Monnify;
use Monnify\MonnifyConfig;

$monnify = new Monnify(MonnifyConfig::sandbox(
    apiKey: 'YOUR_API_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    contractCode: 'YOUR_CONTRACT_CODE',
));

// Retrieve the first page with default size (10 transactions)
$firstPage = $monnify->getAllTransactions();

// Retrieve the second page with 20 transactions per page
$secondPage = $monnify->getAllTransactions(1, 20);

// Retrieve transactions with specific filters
$filtered = $monnify->getAllTransactions(0, 10, [
    'paymentReference' => 'your-payment-reference',
    'customerName' => 'John Doe',
    'customerEmail' => 'john.doe@example.com',
]);
