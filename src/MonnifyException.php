<?php

namespace Monnify;

class MonnifyException extends \RuntimeException
{
    /**
     * @param array<array-key, mixed>|null $responseBody
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly ?int $statusCode = null,
        private readonly ?array $responseBody = null,
        private readonly ?string $rawResponseBody = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function statusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public function responseBody(): ?array
    {
        return $this->responseBody;
    }

    public function rawResponseBody(): ?string
    {
        return $this->rawResponseBody;
    }
}
