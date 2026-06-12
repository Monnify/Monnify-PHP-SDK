<?php

namespace Monnify\Http;

use Monnify\Auth\InMemoryTokenCache;
use Monnify\Auth\TokenCacheInterface;
use Monnify\Contracts\HttpClientInterface;
use Monnify\Enums\HttpMethod;
use Monnify\MonnifyConfig;
use Monnify\MonnifyException;

/**
 * @phpstan-type Payload array<array-key, mixed>
 * @phpstan-type ResponseData array<array-key, mixed>
 */
final class MonnifyApiClient
{
    private TokenCacheInterface $tokenCache;

    public function __construct(
        private MonnifyConfig $config,
        private HttpClientInterface $client,
        ?TokenCacheInterface $tokenCache = null,
    ) {
        $this->tokenCache = $tokenCache ?? new InMemoryTokenCache();
    }

    public function config(): MonnifyConfig
    {
        return $this->config;
    }

    /**
     * @param Payload $data
     * @param Payload $query
     * @return ResponseData
     */
    public function request(HttpMethod $method, string $endpoint, array $data = [], array $query = []): array
    {
        try {
            return $this->sendAuthenticatedRequest($method, $endpoint, $data, $query);
        } catch (MonnifyException $e) {
            if ($e->statusCode() !== 401) {
                throw $e;
            }

            $this->tokenCache->forget();

            return $this->sendAuthenticatedRequest($method, $endpoint, $data, $query);
        }
    }

    /**
     * @param Payload $data
     * @param Payload $query
     * @return ResponseData
     */
    private function sendAuthenticatedRequest(HttpMethod $method, string $endpoint, array $data = [], array $query = []): array
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getBearerToken(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if ($data !== []) {
            $options['json'] = $data;
        }

        if ($query !== []) {
            $options['query'] = $query;
        }

        return $this->client->request($method->value, $endpoint, $options);
    }

    private function getBearerToken(): string
    {
        $cachedToken = $this->tokenCache->get();

        if ($cachedToken !== null) {
            return $cachedToken;
        }

        $data = $this->client->request(HttpMethod::POST->value, '/api/v1/auth/login', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->config->apiKey . ':' . $this->config->secretKey),
                'Accept' => 'application/json',
            ],
        ]);

        $accessToken = $data['responseBody']['accessToken'] ?? null;

        if (! is_string($accessToken) || $accessToken === '') {
            throw new MonnifyException('Monnify authentication response did not include an access token.');
        }

        $expiresIn = $data['responseBody']['expiresIn'] ?? 3600;
        $this->tokenCache->put($accessToken, max(1, (int) $expiresIn - 60));

        return $accessToken;
    }
}
