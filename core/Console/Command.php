<?php

namespace Ylmz\Console;

abstract class Command
{
    protected string $signature = '';
    protected string $description = '';

    abstract public function handle(array $args): int;

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    protected function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m" . PHP_EOL;
    }

    protected function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m" . PHP_EOL;
    }

    protected function warn(string $message): void
    {
        echo "\033[33m{$message}\033[0m" . PHP_EOL;
    }

    protected function line(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
