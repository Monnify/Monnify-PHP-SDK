<?php

use PHPUnit\Framework\TestCase;
use Monnify\Monnify;

class MonnifyTest extends TestCase
{

    private $apiKey = 'MK_TEST_SAF7HR5F3F';
    private $secretKey = '4SY6TNL8CK3VPRSBTHTRG2N8XXEGC6NL';
    private $contractCode = '4934121686';


    public function testInitializeTransaction()
    {
        // Initialize a Monnify instance (you may need to provide a configuration)
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);

        // Your test data for initializing a transaction
        $transactionData = [
            // Your transaction data here
            'amount' => 100.00,
            'currencyCode' => 'USD',
            'customerName' => 'John Doe',
            'customerEmail' => 'john.doe@example.com',
        ];

        // Call the method you want to test
        $result = $monnify->initializeTransaction($transactionData);

        // Add assertions to check if the result is as expected
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }

    public function testChargeCard()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);    
        // Your test data for charging a card
        $transactionReference = 'your_transaction_reference';
        $collectionChannel = 'your_collection_channel';
        $cardData = [
            // Your card data here
        ];
    
        $result = $monnify->chargeCard($transactionReference, $collectionChannel, $cardData);
    
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }
    
    public function testGetTransactionStatus()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);

        $transactionReference = 'your_transaction_reference';
    
        $result = $monnify->getTransactionStatus($transactionReference);
    
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }
    
    public function testGetAllTransactions()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);    
        // Your test data for getting all transactions
        $page = 0;
        $size = 10;
        $filters = [
            // Your filter data here
        ];
    
        $result = $monnify->getAllTransactions($page, $size, $filters);
    
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }
    
    public function testGetAllBanks()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);

        $result = $monnify->getAllBanks();
    
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }
    
    public function testCreateReservedAccount()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);    

        // Your test data for creating a reserved account
        $accountData = [
            // Your account data here
        ];
    
        $result = $monnify->createReservedAccount($accountData);
    
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }
    
    public function testGetReservedAccountDetails()
    {
        $monnify = new Monnify(['api_key' => 'your_api_key', 'secret_key' => 'your_secret_key', 'test' => true]);

        // Replace 'abc1niui--23' with the actual account reference
        $accountReference = 'abc1niui--23';

        $result = $monnify->getReservedAccountDetails($accountReference);

        $this->assertTrue($result['success']);
        $this->assertEquals('success', $result['responseMessage']);

        // Add more assertions to validate the response data
        $this->assertEquals('expected_value', $result['responseBody']['contractCode']);
        $this->assertEquals('expected_value', $result['responseBody']['accountName']);
        // Add more assertions for other response data
    }


    public function testInitiateSingleTransfer()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);    

        // Your test data for initiating a single transfer
        $transferData = [
            // Your transfer data here
        ];
    
        $result = $monnify->initiateSingleTransfer($transferData);
    
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }
    
    public function testInitiateAsyncTransfer()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);    

        // Your test data for initiating an asynchronous transfer
        $transferData = [
            // Your transfer data here
        ];
    
        $result = $monnify->initiateAsyncTransfer($transferData);
    
        $this->assertTrue($result['success']);
        $this->assertEquals('expected_value', $result['some_key']);
    }
    
    public function testValidateWebhook()
    {
        $monnify = new Monnify([
            'api_key' => $this->apiKey, 
            'secret_key' => $this->secretKey,  
            'contract_code' => $this->contractCode,  
            'test' => true
        ]);    

        // Your test data for validating a webhook
        $requestBody = 'webhook_request_data';
        $receivedHash = 'received_hash';
    
        $result = $monnify->validateWebhook($requestBody, $receivedHash);
    
        $this->assertTrue($result);
    }
}
