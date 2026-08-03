<?php

namespace Ylmz\Console;

class ServeCommand extends Command
{
    protected string $signature = 'serve [host] [port]';
    protected string $description = 'Start the development server';

    public function handle(array $args): int
    {
        $host = $args[0] ?? 'localhost';
        $port = $args[1] ?? '8000';

        $this->info("Ylmz development server started at http://{$host}:{$port}");
        $this->line('Press Ctrl+C to stop.');

        $publicPath = YL_ROOT . '/public';
        $command = sprintf(
            'php -S %s:%d -t %s %s/router.php',
            escapeshellarg($host),
            (int)$port,
            escapeshellarg($publicPath),
            escapeshellarg(YL_ROOT)
        );

        passthru($command);
        return 0;
    }
}
