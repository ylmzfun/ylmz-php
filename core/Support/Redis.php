<?php

namespace Ylmz\Support;

class Redis
{
    private static array $instance = [];
    private static array $config = [];

    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    public static function connection(?string $name = null): \Redis
    {
        $name = $name ?? 'default';

        if (isset(self::$instance[$name])) {
            return self::$instance[$name];
        }

        $cfg = self::$config[$name] ?? self::$config['default'] ?? [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 0,
            'prefix' => 'ylmz:',
        ];

        $redis = new \Redis();
        $redis->connect($cfg['host'], (int)($cfg['port'] ?? 6379), 2.5);

        if (!empty($cfg['password'])) {
            $redis->auth($cfg['password']);
        }

        $redis->select((int)($cfg['database'] ?? 0));

        if (!empty($cfg['prefix'])) {
            $redis->setOption(\Redis::OPT_PREFIX, $cfg['prefix']);
        }

        self::$instance[$name] = $redis;
        return $redis;
    }

    public static function isAvailable(): bool
    {
        return extension_loaded('redis');
    }
}
