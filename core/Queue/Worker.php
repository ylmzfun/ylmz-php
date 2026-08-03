<?php

namespace Ylmz\Queue;

use Throwable;

class Worker
{
    private Queue $queue;
    private int $maxAttempts;
    private int $sleep;

    public function __construct(string $queueName = 'default', int $maxAttempts = 3, int $sleep = 3)
    {
        $this->queue = new Queue($queueName);
        $this->maxAttempts = $maxAttempts;
        $this->sleep = $sleep;
    }

    /**
     * Process the next job on the queue.
     */
    public function runNext(): ?array
    {
        $job = $this->queue->pop();

        if (!$job) {
            return null;
        }

        try {
            $this->process($job);
            return $job;
        } catch (Throwable $e) {
            $this->handleFailedJob($job, $e);
            return null;
        }
    }

    /**
     * Run the worker as a daemon, continuously processing jobs.
     */
    public function daemon(): void
    {
        echo '[Ylmz Queue Worker] Started. Processing jobs...' . PHP_EOL;
        echo 'Press Ctrl+C to stop.' . PHP_EOL . PHP_EOL;

        while (true) {
            $job = $this->runNext();

            if ($job) {
                echo sprintf(
                    '[%s] Processed: %s (id: %s)' . PHP_EOL,
                    date('Y-m-d H:i:s'),
                    $job['job'],
                    $job['id']
                );
            } else {
                sleep($this->sleep);
            }
        }
    }

    private function process(array $jobData): void
    {
        $className = $jobData['job'];

        if (!class_exists($className)) {
            throw new \RuntimeException("Job class not found: {$className}");
        }

        /** @var Job $instance */
        $instance = new $className();
        $instance->setPayload($jobData['payload']);
        $instance->handle();
    }

    private function handleFailedJob(array $job, Throwable $e): void
    {
        $job['attempts']++;

        if ($job['attempts'] < $this->maxAttempts) {
            // Re-queue for retry
            $this->queue->push(
                $job['job'],
                $job['payload']
            );
            echo sprintf(
                '[%s] Retrying: %s (attempt %d/%d)' . PHP_EOL,
                date('Y-m-d H:i:s'),
                $job['job'],
                $job['attempts'],
                $this->maxAttempts
            );
        } else {
            $this->queue->fail($job, $e->getMessage());
            echo sprintf(
                '[%s] FAILED: %s - %s' . PHP_EOL,
                date('Y-m-d H:i:s'),
                $job['job'],
                $e->getMessage()
            );
        }
    }
}
