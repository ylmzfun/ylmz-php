<?php

namespace Ylmz;

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
        if (self::$driver === null) {
            self::init();
        }
        return self::$driver->set($key, $value, $ttl);
    }

    public static function get(string $key): mixed
    {
        if (self::$driver === null) {
            self::init();
        }
        return self::$driver->get($key);
    }

    public static function delete(string $key): bool
    {
        if (self::$driver === null) {
            self::init();
        }
        return self::$driver->delete($key);
    }

    public static function clear(): bool
    {
        if (self::$driver === null) {
            self::init();
        }
        return self::$driver->clear();
    }
}
