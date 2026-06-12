<?php

namespace Monnify;

enum Environment: string
{
    case Sandbox = 'SANDBOX';
    case Live = 'LIVE';

    public static function fromConfig(string $environment): self
    {
        return match (strtoupper($environment)) {
            self::Sandbox->value => self::Sandbox,
            self::Live->value => self::Live,
            default => throw new \InvalidArgumentException(
                'Invalid Monnify environment. Expected [SANDBOX] or [LIVE].'
            ),
        };
    }

    public function baseUrl(): string
    {
        return match ($this) {
            self::Sandbox => 'https://sandbox.monnify.com',
            self::Live => 'https://api.monnify.com',
        };
    }
}
