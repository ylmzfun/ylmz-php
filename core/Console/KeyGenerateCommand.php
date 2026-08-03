<?php

namespace Ylmz\Console;

use Ylmz\Foundation\Config;

class KeyGenerateCommand extends Command
{
    protected string $signature = 'key:generate';
    protected string $description = 'Generate a new APP_KEY';

    public function handle(array $args): int
    {
        $key = 'base64:' . base64_encode(random_bytes(32));
        $envFile = YL_ROOT . '/.env';

        if (!file_exists($envFile)) {
            $this->error('.env file not found.');
            return 1;
        }

        $content = file_get_contents($envFile);
        $content = preg_replace(
            '/^APP_KEY=.*$/m',
            'APP_KEY=' . $key,
            $content
        );

        file_put_contents($envFile, $content);
        Config::set('APP_KEY', $key);

        $this->info("APP_KEY generated: {$key}");
        return 0;
    }
}
