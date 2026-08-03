<?php

namespace Ylmz\Console;

class MakeProviderCommand extends Command
{
    protected string $signature = 'make:provider <name>';
    protected string $description = 'Create a new service provider';

    public function handle(array $args): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ylmz make:provider <ProviderName>');
            $this->line('Example: php ylmz make:provider RouteServiceProvider');
            return 1;
        }

        $className = ucfirst($name);
        $namespace = 'App\\Provider';
        $dir = YL_APP . '/Provider';
        $file = $dir . '/' . $className . '.php';

        if (file_exists($file)) {
            $this->error("Provider already exists: {$className}");
            return 1;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stub = $this->getStub($namespace, $className);
        file_put_contents($file, $stub);

        $this->info("Provider created: app/Provider/{$className}.php");
        return 0;
    }

    private function getStub(string $namespace, string $className): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Ylmz\Foundation\ServiceProvider;

class {$className} extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings into the container
    }

    public function boot(): void
    {
        // Perform post-registration booting
    }
}
PHP;
    }
}
