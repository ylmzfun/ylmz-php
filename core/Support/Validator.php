<?php

namespace Ylmz\Support;

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

    public function string(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !is_string($value)) {
            $this->errors[$field][] = $message ?: "The {$field} must be a string.";
        }
        return $this;
    }

    public function boolean(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            $this->errors[$field][] = $message ?: "The {$field} must be true or false.";
        }
        return $this;
    }

    public function date(string $field, string $format = 'Y-m-d', string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && \DateTime::createFromFormat($format, $value) === false) {
            $this->errors[$field][] = $message ?: "The {$field} is not a valid date.";
        }
        return $this;
    }

    public function url(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = $message ?: "The {$field} must be a valid URL.";
        }
        return $this;
    }

    public function ip(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_IP)) {
            $this->errors[$field][] = $message ?: "The {$field} must be a valid IP address.";
        }
        return $this;
    }

    public function regex(string $field, string $pattern, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !preg_match($pattern, $value)) {
            $this->errors[$field][] = $message ?: "The {$field} format is invalid.";
        }
        return $this;
    }

    public function confirmed(string $field, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        $confirm = $this->data[$field . '_confirmation'] ?? '';
        if ($value !== $confirm) {
            $this->errors[$field][] = $message ?: "The {$field} confirmation does not match.";
        }
        return $this;
    }

    public function between(string $field, int $min, int $max, string $message = ''): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '') {
            $len = is_numeric($value) ? (float)$value : strlen((string)$value);
            if ($len < $min || $len > $max) {
                $this->errors[$field][] = $message ?: "The {$field} must be between {$min} and {$max}.";
            }
        }
        return $this;
    }

    public function nullable(string $field): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '') {
            // Remove any previous errors for this field
            unset($this->errors[$field]);
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
