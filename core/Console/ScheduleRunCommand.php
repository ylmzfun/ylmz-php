<?php

namespace Ylmz\Console;

class ScheduleRunCommand extends Command
{
    protected string $signature = 'schedule:run';
    protected string $description = 'Run scheduled tasks';

    public function handle(array $args): int
    {
        $this->info('Running scheduled tasks...');

        // Load schedule from app
        $scheduleFile = YL_APP . '/schedule.php';
        $schedule = new \Ylmz\Support\Schedule();

        if (file_exists($scheduleFile)) {
            require $scheduleFile;
        }

        $results = $schedule->run();

        if (empty($results)) {
            $this->line('No scheduled tasks are due.');
        } else {
            foreach ($results as $result) {
                $this->info("  ✓ {$result['command']}");
            }
        }

        return 0;
    }
}
