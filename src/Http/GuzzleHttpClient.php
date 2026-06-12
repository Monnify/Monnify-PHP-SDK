<?php

namespace Monnify\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use JsonException;
use Monnify\Contracts\HttpClientInterface;
use Monnify\MonnifyException;

final class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClientInterface $client;

    public function __construct(string $baseUri, ?GuzzleClientInterface $client = null)
    {
        $this->client = $client ?? new Client(['base_uri' => $baseUri]);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<array-key, mixed>
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $rawBody = $response !== null ? (string) $response->getBody() : null;

            throw new MonnifyException(
                message: 'Monnify HTTP request failed: ' . $e->getMessage(),
                code: (int) $e->getCode(),
                previous: $e,
                statusCode: $response?->getStatusCode(),
                responseBody: $rawBody !== null ? $this->tryDecodeBody($rawBody) : null,
                rawResponseBody: $rawBody,
            );
        } catch (TransferException $e) {
            throw new MonnifyException('Monnify HTTP request failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        $body = (string) $response->getBody();

        return $this->decodeBody($body);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decodeBody(string $body): array
    {
        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new MonnifyException('Invalid JSON response from Monnify.', 0, $e);
        }

        if (! is_array($data)) {
            throw new MonnifyException('Invalid JSON response from Monnify.');
        }

        return $data;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    private function tryDecodeBody(string $body): ?array
    {
        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($data) ? $data : null;
    }
}
