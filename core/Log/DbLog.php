<?php

namespace Ylmz\Log;

use Ylmz\LogDriver;

class DbLog implements LogDriver
{
    public function write(string $level, string $message): void
    {
        // TODO: implement database logging
    }
}
