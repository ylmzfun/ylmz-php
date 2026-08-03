<?php

namespace Ylmz\Console;

use Ylmz\Schema;

class MigrateCommand extends Command
{
    protected string $signature = 'migrate';
    protected string $description = 'Run all pending migrations';

    public function handle(array $args): int
    {
        $path = YL_APP . '/Migration';

        if (!is_dir($path)) {
            $this->warn('No migrations directory found. Nothing to migrate.');
            return 0;
        }

        $this->info('Running migrations...');
        $migrated = Schema::migrate($path);

        if (empty($migrated)) {
            $this->line('Nothing to migrate.');
        } else {
            foreach ($migrated as $m) {
                $this->info("  ✓ {$m}");
            }
            $this->line('');
            $this->info(count($migrated) . ' migration(s) run.');
        }

        return 0;
    }
}
