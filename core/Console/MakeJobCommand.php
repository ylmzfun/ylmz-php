<?php

namespace Ylmz\Console;

class MakeJobCommand extends Command
{
    protected string $signature = 'make:job <name>';
    protected string $description = 'Create a new job class';

    public function handle(array $args): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ylmz make:job <JobName>');
            $this->line('Example: php ylmz make:job SendEmail');
            return 1;
        }

        $className = ucfirst($name);
        $namespace = 'App\\Job';
        $dir = YL_APP . '/Job';
        $file = $dir . '/' . $className . '.php';

        if (file_exists($file)) {
            $this->error("Job already exists: {$className}");
            return 1;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stub = $this->getStub($namespace, $className);
        file_put_contents($file, $stub);

        $this->info("Job created: app/Job/{$className}.php");
        return 0;
    }

    private function getStub(string $namespace, string $className): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Ylmz\Queue\Job;

class {$className} extends Job
{
    public function handle(): void
    {
        // TODO: Implement job logic
        // Access payload: \$this->payload['key']
    }
}
PHP;
    }
}
