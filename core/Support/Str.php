<?php

namespace Ylmz\Support;

class Str
{
    /** Convert a string to slug format. */
    public static function slug(string $value, string $separator = '-'): string
    {
        $value = preg_replace('/[^\pL\pN]+/u', $separator, $value);
        $value = trim($value, $separator);
        $value = function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
        return $value;
    }

    /** Limit a string to a given length. */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (function_exists('mb_strwidth')) {
            if (mb_strwidth($value, 'UTF-8') <= $limit) return $value;
            return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
        }
        if (strlen($value) <= $limit) return $value;
        return rtrim(substr($value, 0, $limit)) . $end;
    }

    /** Generate a random string. */
    public static function random(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /** Convert a string to snake_case. */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value);
        return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
    }

    /** Convert a string to camelCase. */
    public static function camel(string $value): string
    {
        return lcfirst(static::studly($value));
    }

    /** Convert a string to StudlyCase. */
    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    /** Check if a string contains a substring. */
    public static function contains(string $haystack, string|array $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) return true;
        }
        return false;
    }

    /** Check if a string starts with a substring. */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /** Check if a string ends with a substring. */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }
}
