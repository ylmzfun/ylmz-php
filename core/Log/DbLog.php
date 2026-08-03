<?php

namespace Ylmz\Log;

use Ylmz\LogDriver;
use Ylmz\Model;

class DbLog implements LogDriver
{
    public function write(string $level, string $message): void
    {
        Model::db()->insert('logs', [
            'level' => $level,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
