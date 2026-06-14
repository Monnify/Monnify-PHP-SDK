<?php

namespace Monnify;

/**
 * @phpstan-type ConfigArray array{
 *     api_key: string,
 *     secret_key: string,
 *     contract_code: string,
 *     environment: string,
 *     api_url?: string|null
 * }
 */
final class MonnifyConfig
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $secretKey,
        public readonly string $contractCode,
        public readonly Environment $environment,
        public readonly ?string $apiUrl = null,
    ) {
        if ($apiKey === '') {
            throw new \InvalidArgumentException('Monnify API key is required.');
        }

        if ($secretKey === '') {
            throw new \InvalidArgumentException('Monnify secret key is required.');
        }

        if ($contractCode === '') {
            throw new \InvalidArgumentException('Monnify contract code is required.');
        }
    }

    public static function sandbox(
        string $apiKey,
        string $secretKey,
        string $contractCode,
        ?string $apiUrl = null,
    ): self {
        return new self($apiKey, $secretKey, $contractCode, Environment::Sandbox, $apiUrl);
    }

    public static function live(
        string $apiKey,
        string $secretKey,
        string $contractCode,
        ?string $apiUrl = null,
    ): self {
        return new self($apiKey, $secretKey, $contractCode, Environment::Live, $apiUrl);
    }

    /**
     * @param ConfigArray $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            apiKey: $config['api_key'],
            secretKey: $config['secret_key'],
            contractCode: $config['contract_code'],
            environment: Environment::fromConfig($config['environment']),
            apiUrl: $config['api_url'] ?? null,
        );
    }

    public function baseUrl(): string
    {
        return rtrim($this->apiUrl ?? $this->environment->baseUrl(), '/');
    }
}
