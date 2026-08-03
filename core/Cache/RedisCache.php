<?php

namespace Ylmz\Cache;

use Ylmz\CacheDriver;
use Ylmz\Support\Redis;

class RedisCache implements CacheDriver
{
    private \Redis $redis;

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $data = serialize($value);
        if ($ttl > 0) {
            return $this->redis->setex($key, $ttl, $data);
        }
        return $this->redis->set($key, $data);
    }

    public function get(string $key): mixed
    {
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }
        return unserialize($data);
    }

    public function delete(string $key): bool
    {
        return (bool)$this->redis->del($key);
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }
}
