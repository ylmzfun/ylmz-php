<?php

namespace Ylmz\Queue;

abstract class Job
{
    protected array $payload = [];

    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Execute the job.
     */
    abstract public function handle(): void;
}
