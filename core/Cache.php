<?php

namespace Ylmz;

use Ylmz\Foundation\Config;

class Cache
{
    private static ?CacheDriver $driver = null;

    public static function init(): void
    {
        $driverName = Config::get('CACHE_DRIVER', 'file');
        $driverClass = match ($driverName) {
            'file' => Cache\FileCache::class,
            'redis' => Cache\RedisCache::class,
            'db' => Cache\DbCache::class,
            default => throw new \RuntimeException("Unknown cache driver: {$driverName}"),
        };
        self::$driver = new $driverClass();
    }

    public static function set(string $key, mixed $value, int $ttl = 0): bool
    {
        self::$driver ??= (new self)->init();
        return self::$driver->set($key, $value, $ttl);
    }

    public static function get(string $key): mixed
    {
        self::$driver ??= (new self)->init();
        return self::$driver->get($key);
    }

    public static function delete(string $key): bool
    {
        self::$driver ??= (new self)->init();
        return self::$driver->delete($key);
    }

    public static function clear(): bool
    {
        self::$driver ??= (new self)->init();
        return self::$driver->clear();
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $value = self::get($key);
        if ($value !== null) {
            return $value;
        }
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }
}
