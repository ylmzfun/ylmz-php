<?php

namespace Ylmz\Queue;

class Queue
{
    private \Redis $redis;
    private string $name;

    public function __construct(string $name = 'default')
    {
        $this->redis = \Ylmz\Support\Redis::connection();
        $this->name = 'queue:' . $name;
    }

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
            $this->redis->zAdd($this->name . ':delayed', time() + $delay, $data);
        } else {
            $this->redis->rPush($this->name, $data);
        }

        return $id;
    }

    public function pop(): ?array
    {
        $this->migrateDelayed();
        $data = $this->redis->lPop($this->name);
        return $data ? json_decode($data, true) : null;
    }

    public function size(): int
    {
        return $this->redis->lLen($this->name);
    }

    public function fail(array $job, string $error): void
    {
        $job['failed_at'] = time();
        $job['error'] = $error;
        $this->redis->rPush($this->name . ':failed', json_encode($job));
    }

    public function clear(): void
    {
        $this->redis->del($this->name, $this->name . ':delayed', $this->name . ':failed');
    }

    private function migrateDelayed(): void
    {
        $jobs = $this->redis->zRangeByScore($this->name . ':delayed', 0, time());
        foreach ($jobs as $data) {
            $this->redis->zRem($this->name . ':delayed', $data);
            $this->redis->rPush($this->name, $data);
        }
    }
}
