<?php

namespace Ylmz\Console;

use Ylmz\Queue\Worker;

class QueueWorkCommand extends Command
{
    protected string $signature = 'queue:work [queue]';
    protected string $description = 'Process jobs from the queue';

    public function handle(array $args): int
    {
        $queueName = $args[0] ?? 'default';

        $this->info("Starting queue worker for '{$queueName}'...");
        $this->line('');

        $worker = new Worker($queueName);
        $worker->daemon();

        return 0;
    }
}
