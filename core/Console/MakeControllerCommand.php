<?php

namespace Ylmz\Console;

class MakeControllerCommand extends Command
{
    protected string $signature = 'make:controller <name>';
    protected string $description = 'Create a new controller class';

    public function handle(array $args): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ylmz make:controller <ControllerName>');
            $this->line('Example: php ylmz make:controller User');
            $this->line('Example: php ylmz make:controller Admin/User');
            return 1;
        }

        $parts = explode('/', $name);
        $className = ucfirst(array_pop($parts)) . 'Ctrl';
        $namespace = 'App\\Ctrl';
        $subDir = '';

        if ($parts) {
            $subDir = implode('/', array_map('ucfirst', $parts)) . '/';
            $namespace .= '\\' . implode('\\', array_map('ucfirst', $parts));
        }

        $dir = YL_APP . '/Ctrl/' . $subDir;
        $file = $dir . $className . '.php';

        if (file_exists($file)) {
            $this->error("Controller already exists: {$className}");
            return 1;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stub = $this->getStub($namespace, $className);
        file_put_contents($file, $stub);

        $this->info("Controller created: app/Ctrl/{$subDir}{$className}.php");
        return 0;
    }

    private function getStub(string $namespace, string $className): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Ylmz\Controller;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class {$className} extends Controller
{
    public function index(Request \$request): Response
    {
        return \$this->json(['message' => 'Hello from {$className}']);
    }
}
PHP;
    }
}
