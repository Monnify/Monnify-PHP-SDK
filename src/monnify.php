<?php
namespace Monnify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;

class Monnify
{
    private $client;
    private $apiKey;
    private $apiSecret;
    private $apiUrl;
    private $bearerToken;
    private $contractCode;
 

    public function __construct($config)
    {
        $this->client = new Client();
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['secret_key'];
        $this->contractCode = $config['contract_code'];
        $this->apiUrl = ($config['test'] == true) ? 'https://sandbox.monnify.com' : 'https://api.monnify.com';
        $this->bearerToken = $this->getBearerToken(); // Get the token immediately when the class is instantiated
    }

    private function getBearerToken()
    {
        try {
            $response = $this->client->post($this->apiUrl . '/api/v1/auth/login', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['responseBody']['accessToken'];
        } catch (RequestException $e) {
            // Handle the request exception when getting the Bearer token
            // You can log the error or throw an exception
            throw new Exception("$this->apiUrl  Failed to obtain the Bearer token: " . $e->getMessage());
        }
    }

    private function setAuthorizationHeader()
    {
        return ['Authorization' => 'Bearer ' . $this->bearerToken];
    }

    private function makeRequest($method, $url, $headers, $body = null)
    {
        try {
            $options = [
                'headers' => array_merge(
                    $this->setAuthorizationHeader(),
                    $headers
                ),
            ];

            if ($body !== null) {
                $options['body'] = $body;
            }

            $response = $this->client->request($method, $url, $options);
            // var_dump($response);
            return $this->handleResponse($response);
        } catch (RequestException $e) {
            // Handle the request exception
               throw new Exception("$this->apiUrl  Failed to Make Request: " . $e->getMessage());
        }
    }// undefined handleResponse
    private function handleResponse($response)
{
    // Convert the response body to string and decode JSON
    $body = (string) $response->getBody();

    // Decode it into an associative array
    $data = json_decode($body, true);

    // Optional: check if decoding failed
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response');
    }

    return $data;
}

    public function initializeTransaction($transactionData)
    {
        $url = $this->apiUrl . '/api/v1/merchant/transactions/init-transaction';
        $headers = ['Content-Type' => 'application/json'];
        $body = json_encode($transactionData);
        return $this->makeRequest('POST', $url, $headers, $body);
    }

    public function chargeCard($transactionReference, $collectionChannel, $cardData)
    {
        $url = $this->apiUrl . '/api/v1/merchant/cards/charge';
        $headers = ['Content-Type' => 'application/json'];
        $body = json_encode([
            'transactionReference' => $transactionReference,
            'collectionChannel' => $collectionChannel,
            'card' => $cardData,
        ]);

        return $this->makeRequest('POST', $url, $headers, $body);
    }

    public function getTransactionStatus($transactionReference)
    {
        $urlEncodedTransactionReference = urlencode($transactionReference);
        $url = $this->apiUrl . '/api/v2/transactions/' . $urlEncodedTransactionReference;
        $headers = [];
        
        return $this->makeRequest('GET', $url, $headers);
    }

    public function getAllTransactions($page = 0, $size = 10, $filters = [])
    {
        $queryParams = [
            'page' => $page,
            'size' => $size,
        ];
    
        // Add optional filters
        if (!empty($filters)) {
            $queryParams = array_merge($queryParams, $filters);
        }
    
        $url = $this->apiUrl . '/api/v1/transactions/search';
        $headers = [];
    
        return $this->makeRequest('GET', $url, $headers, null, $queryParams);
    }
    
    public function getAllBanks()
    {
        $url = $this->apiUrl . '/api/v1/banks';
        $headers = [];
    
        return $this->makeRequest('GET', $url, $headers);
    }
    
    public function createReservedAccount($accountData)
    {
        $url = $this->apiUrl . '/api/v2/bank-transfer/reserved-accounts';
        $headers = ['Content-Type' => 'application/json'];

        return $this->makeRequest('POST', $url, $headers, $accountData);
    }

    public function getReservedAccountDetails($accountReference)
    {
        $url = $this->apiUrl . '/api/v2/bank-transfer/reserved-accounts/' . $accountReference;
        $headers = [];
        
        return $this->makeRequest('GET', $url, $headers);
    }

    public function addLinkedAccounts($accountReference, $getAllAvailableBanks, $preferredBanks = [])
    {
        $url = $this->apiUrl . '/api/v1/bank-transfer/reserved-accounts/add-linked-accounts/' . $accountReference;
        $headers = ['Content-Type' => 'application/json'];

        $requestData = [
            'getAllAvailableBanks' => $getAllAvailableBanks,
            'preferredBanks' => $preferredBanks,
        ];

        return $this->makeRequest('PUT', $url, $headers, json_encode($requestData));
    }

    public function getReservedAccountTransactions($accountReference, $page = 0, $size = 10)
    {
        $url = $this->apiUrl . '/api/v1/bank-transfer/reserved-accounts/transactions';

        // Set query parameters
        $query = [
            'accountReference' => $accountReference,
            'page' => $page,
            'size' => $size,
        ];

        $headers = [];

        return $this->makeRequest('GET', $url, $headers, null, $query);
    }

    public function getSingleTransferStatus($reference)
    {
        $url = $this->apiUrl . '/api/v2/disbursements/single/summary';

        // Set query parameters
        $query = ['reference' => $reference];
        $headers = [];

        return $this->makeRequest('GET', $url, $headers, null, $query);
    }

    public function listAllSingleTransfers($pageSize, $pageNo)
    {
        $url = $this->apiUrl . '/api/v2/disbursements/single/transactions';

        // Set query parameters
        $query = [
            'pageSize' => $pageSize,
            'pageNo' => $pageNo,
        ];

        $headers = [];

        return $this->makeRequest('GET', $url, $headers, null, $query);
    }


    public function initiateSingleTransfer($transferData)
    {
        $url = $this->apiUrl . '/api/v2/disbursements/single';
        $headers = ['Content-Type' => 'application/json'];

        return $this->makeRequest('POST', $url, $headers, $transferData);
    }

    public function initiateAsyncTransfer($transferData)
    {
        $url = $this->apiUrl . '/api/v2/disbursements/single';
        $headers = ['Content-Type' => 'application/json'];

        return $this->makeRequest('POST', $url, $headers, $transferData);
    }


    public function validateWebhook($requestBody, $receivedHash)
    {
        // Obtain your Monnify client secret
        $clientSecret = $this->apiSecret; // Replace with your Monnify client secret

        // Compute the expected hash
        $expectedHash = hash_hmac('sha512', $requestBody, $clientSecret);

        // Compare the expected hash with the received hash
        if ($receivedHash === $expectedHash) {
            return true; // Webhook is valid
        } else {
            return false; // Webhook is not valid
        }
    }
}
