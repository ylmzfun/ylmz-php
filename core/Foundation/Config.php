<?php

namespace Ylmz\Foundation;

use Dotenv\Dotenv;

class Config
{
    private static array $items = [];

    public static function load(string $rootPath): void
    {
        $dotenv = Dotenv::createImmutable($rootPath);
        $dotenv->safeLoad();

        self::$items = $_ENV;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$items[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::$items[$key] = $value;
    }

    public static function all(): array
    {
        return self::$items;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower((string)$value), ['true', '1', 'yes', 'on'], true);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }
}
