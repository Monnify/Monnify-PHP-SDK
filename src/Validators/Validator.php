<?php

namespace Monnify\Validators;

use InvalidArgumentException;

/**
 * @phpstan-type Payload array<string, mixed>
 */
abstract class Validator
{
    /**
     * @param Payload $data
     */
    protected function requireString(array $data, string $key): void
    {
        if (! isset($data[$key]) || ! is_string($data[$key]) || $data[$key] === '') {
            throw new InvalidArgumentException("$key is required");
        }
    }

    /**
     * @param Payload $data
     */
    protected function optionalString(array $data, string $key): void
    {
        if (isset($data[$key]) && ! is_string($data[$key])) {
            throw new InvalidArgumentException("$key must be a string");
        }
    }

    /**
     * @param Payload $data
     */
    protected function requireNumeric(array $data, string $key): void
    {
        if (! isset($data[$key]) || ! is_numeric($data[$key])) {
            throw new InvalidArgumentException("$key is required");
        }
    }

    /**
     * @param Payload $data
     */
    protected function optionalNumeric(array $data, string $key): void
    {
        if (isset($data[$key]) && ! is_numeric($data[$key])) {
            throw new InvalidArgumentException("$key must be numeric");
        }
    }

    protected function requireMinimumAmount(float $amount): void
    {
        if ($amount < 20) {
            throw new InvalidArgumentException('amount must be at least 20');
        }
    }

    /**
     * @param Payload $data
     */
    protected function requireEmail(array $data, string $key): void
    {
        if (! isset($data[$key]) || ! is_string($data[$key]) || filter_var($data[$key], FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("$key must be a valid email");
        }
    }

    /**
     * @param Payload $data
     */
    protected function optionalEmail(array $data, string $key): void
    {
        if (isset($data[$key]) && (! is_string($data[$key]) || filter_var($data[$key], FILTER_VALIDATE_EMAIL) === false)) {
            throw new InvalidArgumentException("$key must be a valid email");
        }
    }

    /**
     * @param Payload $data
     */
    protected function requireArray(array $data, string $key): void
    {
        if (! isset($data[$key]) || ! is_array($data[$key])) {
            throw new InvalidArgumentException("$key must be an array");
        }
    }

    /**
     * @param Payload $data
     */
    protected function optionalArray(array $data, string $key): void
    {
        if (isset($data[$key]) && ! is_array($data[$key])) {
            throw new InvalidArgumentException("$key must be an array");
        }
    }

    /**
     * @param Payload $data
     */
    protected function requireBoolean(array $data, string $key): void
    {
        if (! isset($data[$key]) || ! is_bool($data[$key])) {
            throw new InvalidArgumentException("$key must be boolean");
        }
    }

    /**
     * @param Payload $data
     */
    protected function optionalBoolean(array $data, string $key): void
    {
        if (isset($data[$key]) && ! is_bool($data[$key])) {
            throw new InvalidArgumentException("$key must be boolean");
        }
    }

    /**
     * @param Payload $data
     */
    protected function optionalInteger(array $data, string $key): void
    {
        if (isset($data[$key]) && ! is_int($data[$key])) {
            throw new InvalidArgumentException("$key must be an integer");
        }
    }

    /**
     * @param Payload $data
     */
    protected function optionalTimestamp(array $data, string $key): void
    {
        if (! isset($data[$key])) {
            return;
        }

        $value = (string) $data[$key];
        if (! ctype_digit($value) || strlen($value) !== 13) {
            throw new InvalidArgumentException("$key must be a 13-digit timestamp");
        }
    }

    /**
     * @param Payload $data
     */
    protected function requireNestedString(array $data, string $parent, string $key): void
    {
        if (! isset($data[$parent]) || ! is_array($data[$parent])) {
            throw new InvalidArgumentException("$parent must be an array");
        }

        $this->requireString($data[$parent], $key);
    }
}
