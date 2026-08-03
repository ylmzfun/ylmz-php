<?php

namespace Ylmz;

use Ylmz\Foundation\Config;

class Log
{
    private static ?LogDriver $driver = null;

    public static function init(): void
    {
        $driverName = Config::get('LOG_DRIVER', 'file');
        $driverClass = match ($driverName) {
            'file' => Log\FileLog::class,
            'db' => Log\DbLog::class,
            default => throw new \RuntimeException("Unknown log driver: {$driverName}"),
        };
        self::$driver = new $driverClass();
    }

    public static function info(string $message): void    { self::write('INFO', $message); }
    public static function error(string $message): void   { self::write('ERROR', $message); }
    public static function warning(string $message): void { self::write('WARNING', $message); }
    public static function debug(string $message): void   { self::write('DEBUG', $message); }

    private static function write(string $level, string $message): void
    {
        self::$driver ??= (new self)->init();
        self::$driver->write($level, $message);
    }
}
