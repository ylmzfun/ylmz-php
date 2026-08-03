<?php

namespace Ylmz\Cache;

use Ylmz\CacheDriver;

class RedisCache implements CacheDriver
{
    // TODO: implement Redis caching via phpredis
    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        return false;
    }

    public function get(string $key): mixed
    {
        return null;
    }

    public function delete(string $key): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return false;
    }
}
