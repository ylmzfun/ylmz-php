<?php

namespace Ylmz;

interface CacheDriver
{
    public function set(string $key, mixed $value, int $ttl = 0): bool;
    public function get(string $key): mixed;
    public function delete(string $key): bool;
    public function clear(): bool;
}
