<?php

namespace Ylmz\Console;

use Ylmz\Schema;

class MigrateRollbackCommand extends Command
{
    protected string $signature = 'migrate:rollback';
    protected string $description = 'Rollback the last migration batch';

    public function handle(array $args): int
    {
        $path = YL_APP . '/Migration';

        if (!is_dir($path)) {
            $this->warn('No migrations found.');
            return 0;
        }

        $this->info('Rolling back...');
        $rolled = Schema::rollback($path);

        if (empty($rolled)) {
            $this->line('Nothing to rollback.');
        } else {
            foreach ($rolled as $m) {
                $this->info("  ↺ {$m}");
            }
            $this->line('');
            $this->info(count($rolled) . ' migration(s) rolled back.');
        }

        return 0;
    }
}
