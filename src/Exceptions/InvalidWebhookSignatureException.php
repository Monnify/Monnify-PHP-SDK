<?php

namespace Monnify\Exceptions;

use RuntimeException;

final class InvalidWebhookSignatureException extends RuntimeException
{
    public static function missingSignature(): self
    {
        return new self('The Monnify webhook signature header is missing.');
    }

    public static function missingSecret(): self
    {
        return new self('The Monnify webhook secret key is not configured.');
    }

    public static function invalidSignature(): self
    {
        return new self('The Monnify webhook signature is invalid.');
    }
}
