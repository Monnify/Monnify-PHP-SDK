<?php

namespace Monnify\Auth;

final class InMemoryTokenCache implements TokenCacheInterface
{
    private ?string $token = null;
    private ?int $expiresAt = null;

    public function get(): ?string
    {
        if ($this->token === null || $this->expiresAt === null || $this->expiresAt <= time()) {
            return null;
        }

        return $this->token;
    }

    public function put(string $token, int $expiresIn): void
    {
        $this->token = $token;
        $this->expiresAt = time() + $expiresIn;
    }

    public function forget(): void
    {
        $this->token = null;
        $this->expiresAt = null;
    }
}
