<?php

namespace Ylmz\Console;

class MakeMigrationCommand extends Command
{
    protected string $signature = 'make:migration <name>';
    protected string $description = 'Create a new migration file';

    public function handle(array $args): int
    {
        $name = $args[0] ?? null;

        if (!$name) {
            $this->error('Usage: php ylmz make:migration <MigrationName>');
            $this->line('Example: php ylmz make:migration create_users_table');
            return 1;
        }

        $timestamp = date('YmdHis');
        $filename = $timestamp . '_' . strtolower($name);
        $className = implode('', array_map('ucfirst', explode('_', $name)));

        $dir = YL_APP . '/Migration';
        $file = $dir . '/' . $filename . '.php';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $stub = $this->getStub($className);
        file_put_contents($file, $stub);

        $this->info("Migration created: app/Migration/{$filename}.php");
        return 0;
    }

    private function getStub(string $className): string
    {
        return <<<PHP
<?php

namespace App\Migration;

class {$className}
{
    public function up(): void
    {
        // Example:
        // \\Ylmz\\Model::db()->query("
        //     CREATE TABLE example (
        //         id INT AUTO_INCREMENT PRIMARY KEY,
        //         name VARCHAR(255) NOT NULL,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        //         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        //     )
        // ");
    }

    public function down(): void
    {
        // \\Ylmz\\Model::db()->query("DROP TABLE IF EXISTS example");
    }
}
PHP;
    }
}
