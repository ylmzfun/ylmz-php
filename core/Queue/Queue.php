<?php

namespace Ylmz\Queue;

use Ylmz\Redis;

class Queue
{
    private \Redis $redis;
    private string $queueName;

    public function __construct(string $queueName = 'default')
    {
        $this->redis = Redis::connection();
        $this->queueName = 'queue:' . $queueName;
    }

    /**
     * Push a job onto the queue.
     */
    public function push(string $jobClass, array $payload = [], ?int $delay = null): string
    {
        $data = json_encode([
            'id' => $id = uniqid('job_', true),
            'job' => $jobClass,
            'payload' => $payload,
            'attempts' => 0,
            'created_at' => time(),
        ]);

        if ($delay && $delay > 0) {
            $this->redis->zAdd($this->queueName . ':delayed', time() + $delay, $data);
        } else {
            $this->redis->rPush($this->queueName, $data);
        }

        return $id;
    }

    /**
     * Pop the next job from the queue.
     */
    public function pop(): ?array
    {
        // Process delayed jobs that are ready
        $this->migrateDelayedJobs();

        $data = $this->redis->lPop($this->queueName);
        if (!$data) {
            return null;
        }

        return json_decode($data, true);
    }

    /**
     * Get queue size.
     */
    public function size(): int
    {
        return $this->redis->lLen($this->queueName);
    }

    /**
     * Move a failed job to the failed queue.
     */
    public function fail(array $job, string $error): void
    {
        $job['failed_at'] = time();
        $job['error'] = $error;
        $this->redis->rPush($this->queueName . ':failed', json_encode($job));
    }

    /**
     * Clear the queue.
     */
    public function clear(): void
    {
        $this->redis->del($this->queueName);
        $this->redis->del($this->queueName . ':delayed');
        $this->redis->del($this->queueName . ':failed');
    }

    private function migrateDelayedJobs(): void
    {
        $now = time();
        $jobs = $this->redis->zRangeByScore($this->queueName . ':delayed', 0, $now);

        foreach ($jobs as $data) {
            $this->redis->zRem($this->queueName . ':delayed', $data);
            $this->redis->rPush($this->queueName, $data);
        }
    }
}
