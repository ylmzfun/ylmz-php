<?php

namespace Ylmz\Console;

use Ylmz\Schema;

class MigrateStatusCommand extends Command
{
    protected string $signature = 'migrate:status';
    protected string $description = 'Show migration status';

    public function handle(array $args): int
    {
        $path = YL_APP . '/Migration';

        if (!is_dir($path)) {
            $this->warn('No migrations found.');
            return 0;
        }

        $status = Schema::status($path);

        if (empty($status)) {
            $this->line('No migrations found.');
            return 0;
        }

        $this->info('Migration Status:');
        $this->line(str_repeat('-', 70));
        printf("  %-40s %-8s %s\n", 'Migration', 'Status', 'Batch');
        $this->line(str_repeat('-', 70));

        foreach ($status as $row) {
            printf(
                "  %-40s %-8s %s\n",
                $row['migration'],
                $row['ran'] ? 'Ran' : 'Pending',
                $row['batch'] ?? '-'
            );
        }

        return 0;
    }
}
