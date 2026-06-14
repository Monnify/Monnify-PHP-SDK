<?php

namespace Monnify\Webhooks;

use Monnify\Exceptions\InvalidWebhookSignatureException;

final class WebhookSignatureVerifier
{
    public const SIGNATURE_HEADER = 'monnify-signature';

    public function __construct(private readonly string $secretKey)
    {
        if ($secretKey === '') {
            throw InvalidWebhookSignatureException::missingSecret();
        }
    }

    public function verify(string $requestBody, ?string $signature): void
    {
        if ($signature === null || $signature === '') {
            throw InvalidWebhookSignatureException::missingSignature();
        }

        if (! $this->isValid($requestBody, $signature)) {
            throw InvalidWebhookSignatureException::invalidSignature();
        }
    }

    public function isValid(string $requestBody, ?string $signature): bool
    {
        if ($signature === null || $signature === '') {
            return false;
        }

        return hash_equals($this->expectedSignature($requestBody), $signature);
    }

    public function expectedSignature(string $requestBody): string
    {
        return hash_hmac('sha512', $requestBody, $this->secretKey);
    }
}
