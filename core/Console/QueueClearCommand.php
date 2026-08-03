<?php

namespace Ylmz\Console;

use Ylmz\Queue\Queue;

class QueueClearCommand extends Command
{
    protected string $signature = 'queue:clear [queue]';
    protected string $description = 'Clear all jobs from a queue';

    public function handle(array $args): int
    {
        $queueName = $args[0] ?? 'default';

        $queue = new Queue($queueName);
        $queue->clear();

        $this->info("Queue '{$queueName}' cleared.");
        return 0;
    }
}
