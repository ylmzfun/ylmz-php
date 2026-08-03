<?php

namespace Ylmz\Console;

class MakeModelCommand extends Command
{
    protected string $signature = 'make:model <name>';
    protected string $description = 'Create a new model class';

    public function handle(array $args): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ylmz make:model <ModelName>');
            $this->line('Example: php ylmz make:model User');
            $this->line('Example: php ylmz make:model Blog/Post');
            return 1;
        }

        $parts = explode('/', $name);
        $className = ucfirst(array_pop($parts));
        $namespace = 'App\\Model';
        $subDir = '';

        if ($parts) {
            $subDir = implode('/', array_map('ucfirst', $parts)) . '/';
            $namespace .= '\\' . implode('\\', array_map('ucfirst', $parts));
        }

        $dir = YL_APP . '/Model/' . $subDir;
        $file = $dir . $className . '.php';

        if (file_exists($file)) {
            $this->error("Model already exists: {$className}");
            return 1;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stub = $this->getStub($namespace, $className);
        file_put_contents($file, $stub);

        $this->info("Model created: app/Model/{$subDir}{$className}.php");
        return 0;
    }

    private function getStub(string $namespace, string $className): string
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_\\0', $className));
        return <<<PHP
<?php

namespace {$namespace};

use Ylmz\Model;

class {$className} extends Model
{
    protected string \$table = '{$table}';

    public function getAll(): array
    {
        return self::db()->select(\$this->table, '*');
    }

    public function getById(int \$id): ?array
    {
        return self::db()->get(\$this->table, '*', ['id' => \$id]);
    }
}
PHP;
    }
}
