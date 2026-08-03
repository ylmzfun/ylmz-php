<?php

namespace Ylmz;

interface LogDriver
{
    public function write(string $level, string $message): void;
}
