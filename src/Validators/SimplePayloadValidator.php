<?php

namespace Monnify\Validators;

use InvalidArgumentException;

/**
 * @phpstan-type Payload array<string, mixed>
 */
final class SimplePayloadValidator extends Validator
{
    /**
     * @param Payload $data
     */
    public function requireString(array $data, string $key): void
    {
        parent::requireString($data, $key);
    }

    /**
     * @param Payload $data
     */
    public function optionalString(array $data, string $key): void
    {
        parent::optionalString($data, $key);
    }

    /**
     * @param Payload $data
     */
    public function requireNumeric(array $data, string $key): void
    {
        parent::requireNumeric($data, $key);
    }

    /**
     * @param Payload $data
     */
    public function optionalArray(array $data, string $key): void
    {
        parent::optionalArray($data, $key);
    }

    /**
     * @param Payload $data
     * @param list<string> $fields
     */
    public function requireStrings(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            $this->requireString($data, $field);
        }
    }

    /**
     * @param Payload $data
     * @param list<string> $fields
     */
    public function optionalStrings(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            $this->optionalString($data, $field);
        }
    }

    /**
     * @param Payload $data
     * @param list<string> $fields
     */
    public function requireNumerics(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            $this->requireNumeric($data, $field);
        }
    }

    /**
     * @param Payload $data
     * @param list<string> $fields
     */
    public function optionalNumerics(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            $this->optionalNumeric($data, $field);
        }
    }

    /**
     * @param Payload $data
     */
    public function requireMinimum(array $data, string $field, float $minimum): void
    {
        $this->requireNumeric($data, $field);

        if ((float) $data[$field] < $minimum) {
            throw new InvalidArgumentException("$field must be at least $minimum");
        }
    }

    /**
     * @param Payload $data
     */
    public function optionalMinimum(array $data, string $field, float $minimum): void
    {
        if (! isset($data[$field])) {
            return;
        }

        $this->optionalNumeric($data, $field);

        if ((float) $data[$field] < $minimum) {
            throw new InvalidArgumentException("$field must be at least $minimum");
        }
    }

    /**
     * @param Payload $data
     */
    public function requireEmailField(array $data, string $field): void
    {
        $this->requireEmail($data, $field);
    }

    /**
     * @param Payload $data
     * @param list<string> $fields
     */
    public function optionalBooleans(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (! isset($data[$field])) {
                continue;
            }

            if (! is_bool($data[$field]) && ! in_array($data[$field], [0, 1, '0', '1'], true)) {
                throw new InvalidArgumentException("$field must be boolean");
            }
        }
    }

    /**
     * @param Payload $data
     * @param list<string> $fields
     */
    public function optionalIntegers(array $data, array $fields): void
    {
        foreach ($fields as $field) {
            if (! isset($data[$field])) {
                continue;
            }

            if (! is_int($data[$field]) && (! is_string($data[$field]) || filter_var($data[$field], FILTER_VALIDATE_INT) === false)) {
                throw new InvalidArgumentException("$field must be an integer");
            }
        }
    }
}
