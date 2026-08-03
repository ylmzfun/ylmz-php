<?php

namespace Ylmz\Console;

class MakeCommandCommand extends Command
{
    protected string $signature = 'make:command <name>';
    protected string $description = 'Create a new CLI command class';

    public function handle(array $args): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ylmz make:command <CommandName>');
            $this->line('Example: php ylmz make:command SendReport');
            return 1;
        }

        $className = ucfirst($name);
        $namespace = 'App\\Command';
        $dir = YL_APP . '/Command';
        $file = $dir . '/' . $className . '.php';

        if (file_exists($file)) {
            $this->error("Command already exists: {$className}");
            return 1;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stub = $this->getStub($namespace, $className);
        file_put_contents($file, $stub);

        $this->info("Command created: app/Command/{$className}.php");
        $this->line('  Register it in the ylmz CLI file to use it.');
        return 0;
    }

    private function getStub(string $namespace, string $className): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Ylmz\Console\Command;

class {$className} extends Command
{
    protected string \$signature = '{$className}';
    protected string \$description = 'Description of the command';

    public function handle(array \$args): int
    {
        \$this->info('Hello from {$className}!');
        return 0;
    }
}
PHP;
    }
}
