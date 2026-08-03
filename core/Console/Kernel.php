<?php

namespace Ylmz\Console;

class Kernel
{
    private array $commands = [];

    public function register(string $name, string $class): void
    {
        $this->commands[$name] = $class;
    }

    public function run(array $argv): int
    {
        $script = array_shift($argv);
        $commandName = array_shift($argv) ?? 'list';

        if ($commandName === 'list' || $commandName === 'help') {
            return $this->showHelp();
        }

        if (!isset($this->commands[$commandName])) {
            echo "Command not found: {$commandName}" . PHP_EOL;
            $this->showHelp();
            return 1;
        }

        $class = $this->commands[$commandName];
        /** @var Command $instance */
        $instance = new $class();
        return $instance->handle($argv);
    }

    private function showHelp(): int
    {
        echo "Ylmz Framework CLI" . PHP_EOL;
        echo str_repeat('-', 40) . PHP_EOL;
        echo "Usage: php ylmz <command> [arguments]" . PHP_EOL . PHP_EOL;
        echo "Available commands:" . PHP_EOL;

        foreach ($this->commands as $name => $class) {
            $instance = new $class();
            printf("  %-25s %s" . PHP_EOL, $name, $instance->getDescription());
        }

        echo PHP_EOL;
        printf("  %-25s %s" . PHP_EOL, 'list', 'Show this help');
        echo PHP_EOL;

        return 0;
    }
}
