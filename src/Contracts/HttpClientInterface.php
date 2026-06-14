<?php

namespace Monnify\Contracts;

interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $options
     * @return array<array-key, mixed>
     */
    public function request(string $method, string $uri, array $options = []): array;
}
