<?php

namespace Ylmz\Console;

abstract class Command
{
    protected string $signature = '';
    protected string $description = '';

    abstract public function handle(array $args): int;

    public function getSignature(): string    { return $this->signature; }
    public function getDescription(): string { return $this->description; }

    protected function info(string $msg): void  { echo "\033[32m{$msg}\033[0m" . PHP_EOL; }
    protected function error(string $msg): void { echo "\033[31m{$msg}\033[0m" . PHP_EOL; }
    protected function warn(string $msg): void  { echo "\033[33m{$msg}\033[0m" . PHP_EOL; }
    protected function line(string $msg): void  { echo $msg . PHP_EOL; }
}
