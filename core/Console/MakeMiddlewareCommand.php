<?php

namespace Ylmz\Console;

class MakeMiddlewareCommand extends Command
{
    protected string $signature = 'make:middleware <name>';
    protected string $description = 'Create a new middleware class';

    public function handle(array $args): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ylmz make:middleware <MiddlewareName>');
            $this->line('Example: php ylmz make:middleware Auth');
            return 1;
        }

        $className = ucfirst($name);
        $namespace = 'App\\Middleware';
        $dir = YL_APP . '/Middleware';
        $file = $dir . '/' . $className . '.php';

        if (file_exists($file)) {
            $this->error("Middleware already exists: {$className}");
            return 1;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stub = $this->getStub($namespace, $className);
        file_put_contents($file, $stub);

        $this->info("Middleware created: app/Middleware/{$className}.php");
        return 0;
    }

    private function getStub(string $namespace, string $className): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Closure;
use Ylmz\Http\Middleware;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class {$className} implements Middleware
{
    public function handle(Request \$request, Closure \$next): Response
    {
        // TODO: Add your middleware logic here
        return \$next(\$request);
    }
}
PHP;
    }
}
