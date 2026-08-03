<?php

namespace Ylmz\Support;

class Schedule
{
    private array $events = [];

    /**
     * Schedule a command to run at a cron expression.
     */
    public function command(string $command, string $cron = '* * * * *'): ScheduleEvent
    {
        $event = new ScheduleEvent($command, $cron);
        $this->events[] = $event;
        return $event;
    }

    /**
     * Schedule a closure to run at a cron expression.
     */
    public function call(callable $callback, string $cron = '* * * * *'): ScheduleEvent
    {
        $id = 'closure_' . count($this->events);
        $event = new ScheduleEvent($id, $cron, $callback);
        $this->events[] = $event;
        return $event;
    }

    /**
     * Run due events. Called by schedule:run CLI command.
     */
    public function run(): array
    {
        $results = [];

        foreach ($this->events as $event) {
            if ($event->isDue()) {
                $results[] = [
                    'command' => $event->getCommand(),
                    'output' => $event->run(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get all registered events.
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}

class ScheduleEvent
{
    private string $command;
    private string $cron;
    /** @var callable|null */
    private $callback;
    private string $output = '';
    private bool $withoutOverlapping = false;
    private ?string $lockFile = null;
    private ?string $runInBackground = null;

    public function __construct(string $command, string $cron, ?callable $callback = null)
    {
        $this->command = $command;
        $this->cron = $cron;
        $this->callback = $callback;
    }

    public function getCommand(): string { return $this->command; }

    /**
     * Check if the event is due to run based on cron expression.
     */
    public function isDue(): bool
    {
        return self::cronMatches($this->cron);
    }

    /**
     * Prevent overlapping executions.
     */
    public function withoutOverlapping(): self
    {
        $this->withoutOverlapping = true;
        $this->lockFile = RUNTIME_PATH . '/schedule_' . md5($this->command) . '.lock';
        return $this;
    }

    /**
     * Run in background.
     */
    public function runInBackground(): self
    {
        $this->runInBackground = ' > /dev/null 2>&1 &';
        return $this;
    }

    /**
     * Send output to a file.
     */
    public function sendOutputTo(string $path): self
    {
        $this->runInBackground = ' >> ' . escapeshellarg($path) . ' 2>&1 &';
        return $this;
    }

    /**
     * Execute the event.
     */
    public function run(): string
    {
        if ($this->withoutOverlapping && file_exists($this->lockFile)) {
            return 'Skipped (overlapping)';
        }

        if ($this->withoutOverlapping) {
            touch($this->lockFile);
        }

        if ($this->callback) {
            ob_start();
            ($this->callback)();
            $output = ob_get_clean();
        } else {
            $output = shell_exec($this->command . ($this->runInBackground ?? ' 2>&1'));
        }

        return $output ?: '';
    }

    /**
     * Match a cron expression against current time.
     */
    public static function cronMatches(string $cron): bool
    {
        $now = new \DateTime();
        $parts = preg_split('/\s+/', trim($cron));

        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $weekday] = $parts;

        return self::cronPartMatches($minute, (int)$now->format('i'))
            && self::cronPartMatches($hour, (int)$now->format('G'))
            && self::cronPartMatches($day, (int)$now->format('j'))
            && self::cronPartMatches($month, (int)$now->format('n'))
            && self::cronPartMatches($weekday, (int)$now->format('w'));
    }

    private static function cronPartMatches(string $pattern, int $value): bool
    {
        if ($pattern === '*') return true;

        foreach (explode(',', $pattern) as $part) {
            if ($part === (string)$value) return true;

            if (str_contains($part, '/')) {
                [$range, $step] = explode('/', $part, 2);
                $step = (int)$step;
                if ($range === '*') {
                    if ($value % $step === 0) return true;
                }
            }

            if (str_contains($part, '-')) {
                [$from, $to] = explode('-', $part, 2);
                if ($value >= (int)$from && $value <= (int)$to) return true;
            }
        }
        return false;
    }
}
