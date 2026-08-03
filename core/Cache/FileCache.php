<?php

namespace Ylmz\Cache;

use Ylmz\CacheDriver;
use Ylmz\Foundation\Config;

class FileCache implements CacheDriver
{
    private string $path;

    public function __construct()
    {
        $this->path = Config::get('CACHE_PATH', RUNTIME_PATH . '/cache/');
        $this->path = rtrim($this->path, '/') . '/';
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $file = $this->path . sha1($key) . '.cache';
        $data = json_encode([
            'value' => $value,
            'expire' => $ttl > 0 ? time() + $ttl : 0,
        ]);
        return file_put_contents($file, $data) !== false;
    }

    public function get(string $key): mixed
    {
        $file = $this->path . sha1($key) . '.cache';
        if (!is_file($file)) {
            return null;
        }

        $data = json_decode(file_get_contents($file), true);
        if ($data === null) {
            return null;
        }

        if ($data['expire'] > 0 && $data['expire'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    public function delete(string $key): bool
    {
        $file = $this->path . sha1($key) . '.cache';
        if (is_file($file)) {
            return unlink($file);
        }
        return false;
    }

    public function clear(): bool
    {
        if (!is_dir($this->path)) {
            return true;
        }

        $files = glob($this->path . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
}
