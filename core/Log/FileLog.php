<?php

namespace Ylmz\Log;

use Ylmz\LogDriver;
use Ylmz\Foundation\Config;

class FileLog implements LogDriver
{
    private string $path;

    public function __construct()
    {
        $this->path = Config::get('LOG_PATH', RUNTIME_PATH . '/log/');
        $this->path = rtrim($this->path, '/') . '/';
    }

    public function write(string $level, string $message): void
    {
        $dir = $this->path . date('Ymd');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . '/' . strtolower($level) . '.log';
        $line = sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), $level, $message);
        file_put_contents($file, $line . PHP_EOL, FILE_APPEND);
    }
}
