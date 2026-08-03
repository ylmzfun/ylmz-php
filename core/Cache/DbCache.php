<?php

namespace Ylmz\Cache;

use Ylmz\CacheDriver;
use Ylmz\Model;

class DbCache implements CacheDriver
{
    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $hash = sha1($key);
        $expire = $ttl > 0 ? time() + $ttl : 0;

        Model::db()->delete('cache', ['key' => $hash]);
        Model::db()->insert('cache', [
            'key' => $hash,
            'value' => serialize($value),
            'expire' => $expire,
        ]);

        return true;
    }

    public function get(string $key): mixed
    {
        $hash = sha1($key);
        $row = Model::db()->get('cache', ['value', 'expire'], ['key' => $hash]);

        if (!$row) {
            return null;
        }

        if ($row['expire'] > 0 && $row['expire'] < time()) {
            Model::db()->delete('cache', ['key' => $hash]);
            return null;
        }

        return unserialize($row['value']);
    }

    public function delete(string $key): bool
    {
        return (bool)Model::db()->delete('cache', ['key' => sha1($key)]);
    }

    public function clear(): bool
    {
        return (bool)Model::db()->delete('cache', []);
    }
}
