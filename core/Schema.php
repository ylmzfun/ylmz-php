<?php

namespace Ylmz;

class Schema
{
    private static string $table = 'migrations';

    /**
     * Create the migrations table if it doesn't exist.
     */
    public static function init(): void
    {
        $exists = Model::db()->query(
            "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'migrations'"
        )->fetch();

        if (!$exists) {
            Model::db()->query("
                CREATE TABLE migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    batch INT NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
    }

    /**
     * Run all pending migrations.
     */
    public static function migrate(string $path): array
    {
        self::init();

        $ran = Model::db()->select('migrations', 'migration');
        $ran = array_column($ran, 'migration');

        $files = glob($path . '/*.php');
        sort($files);

        $batch = (int)(Model::db()->max('migrations', 'batch') ?: 0) + 1;
        $migrated = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran, true)) {
                continue;
            }

            require_once $file;

            $class = self::resolveClass($name);
            if (!class_exists($class)) {
                continue;
            }

            $instance = new $class();
            $instance->up();

            Model::db()->insert('migrations', [
                'migration' => $name,
                'batch' => $batch,
            ]);

            $migrated[] = $name;
        }

        return $migrated;
    }

    /**
     * Rollback the last batch of migrations.
     */
    public static function rollback(string $path): array
    {
        self::init();

        $lastBatch = Model::db()->max('migrations', 'batch');
        if (!$lastBatch) {
            return [];
        }

        $rows = Model::db()->select('migrations', 'migration', ['batch' => $lastBatch]);
        $rolled = [];

        foreach (array_reverse($rows) as $row) {
            $name = $row['migration'];
            $file = $path . '/' . $name . '.php';

            if (file_exists($file)) {
                require_once $file;
                $class = self::resolveClass($name);
                if (class_exists($class)) {
                    (new $class())->down();
                }
            }

            Model::db()->delete('migrations', ['migration' => $name]);
            $rolled[] = $name;
        }

        return $rolled;
    }

    /**
     * Get all migrations and their status.
     */
    public static function status(string $path): array
    {
        self::init();

        $ran = Model::db()->select('migrations', ['migration', 'batch', 'created_at']);
        $ranMap = [];
        foreach ($ran as $r) {
            $ranMap[$r['migration']] = $r;
        }

        $files = glob($path . '/*.php');
        sort($files);

        $result = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $result[] = [
                'migration' => $name,
                'ran' => isset($ranMap[$name]),
                'batch' => $ranMap[$name]['batch'] ?? null,
                'created_at' => $ranMap[$name]['created_at'] ?? null,
            ];
        }

        return $result;
    }

    private static function resolveClass(string $filename): string
    {
        // 20240101000000_create_users_table → CreateUsersTable
        $parts = explode('_', $filename);
        array_shift($parts); // remove timestamp prefix
        $className = implode('', array_map('ucfirst', $parts));
        return 'App\\Migration\\' . $className;
    }
}
