<?php

namespace Ylmz;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '') {
            $this->errors[$field][] = $message ?: "The {$field} field is required.";
        }
        return $this;
    }

    public function email(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?: "The {$field} must be a valid email address.";
        }
        return $this;
    }

    public function min(string $field, int $min, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (is_string($value) && strlen($value) < $min) {
            $this->errors[$field][] = $message ?: "The {$field} must be at least {$min} characters.";
        }
        if (is_numeric($value) && $value < $min) {
            $this->errors[$field][] = $message ?: "The {$field} must be at least {$min}.";
        }
        return $this;
    }

    public function max(string $field, int $max, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (is_string($value) && strlen($value) > $max) {
            $this->errors[$field][] = $message ?: "The {$field} must not exceed {$max} characters.";
        }
        if (is_numeric($value) && $value > $max) {
            $this->errors[$field][] = $message ?: "The {$field} must not exceed {$max}.";
        }
        return $this;
    }

    public function numeric(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field][] = $message ?: "The {$field} must be numeric.";
        }
        return $this;
    }

    public function in(string $field, array $values, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !in_array($value, $values, true)) {
            $this->errors[$field][] = $message ?: "The {$field} must be one of: " . implode(', ', $values) . ".";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }
        return null;
    }
}
