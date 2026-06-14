<?php

namespace Monnify\Auth;

interface TokenCacheInterface
{
    public function get(): ?string;

    public function put(string $token, int $expiresIn): void;

    public function forget(): void;
}
