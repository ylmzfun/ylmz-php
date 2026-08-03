<?php

namespace Ylmz\Support;

class Hash
{
    /**
     * Create a bcrypt hash.
     */
    public static function make(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_BCRYPT, $options ?: ['cost' => 12]);
    }

    /**
     * Verify a value against a hash.
     */
    public static function check(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    /**
     * Check if a hash needs rehashing.
     */
    public static function needsRehash(string $hash, array $options = []): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $options ?: ['cost' => 12]);
    }
}
