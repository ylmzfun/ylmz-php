<?php

namespace Ylmz;

use Medoo\Medoo;
use Ylmz\Foundation\Config;

class Model
{
    protected static ?Medoo $db = null;

    public static function setDatabase(Medoo $database): void
    {
        self::$db = $database;
    }

    public static function db(): Medoo
    {
        if (self::$db === null) {
            $config = [
                'type' => Config::get('DB_TYPE', 'mysql'),
                'host' => Config::get('DB_HOST', 'localhost'),
                'port' => Config::getInt('DB_PORT', 3306),
                'database' => Config::get('DB_NAME', 'test'),
                'username' => Config::get('DB_USER', 'root'),
                'password' => Config::get('DB_PASS', ''),
            ];
            self::$db = new Medoo($config);
        }
        return self::$db;
    }
}
