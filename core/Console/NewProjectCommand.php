<?php

namespace Ylmz\Console;

class NewProjectCommand extends Command
{
    protected string $signature = 'new <project-name>';
    protected string $description = 'Create a new Ylmz project';

    private string $sourceRoot;
    private string $targetRoot;

    /** Files to copy from framework kernel */
    private array $coreFiles = [
        'composer.json',
        '.env.example',
        '.gitignore',
        '.htaccess',
        'README.md',
        'ylmz',
    ];

    /** Directories copied entirely from kernel */
    private array $copyDirs = [
        'core' => 'core',
    ];

    /** Empty dirs to create in new project */
    private array $emptyDirs = [
        'app/Ctrl',
        'app/Model',
        'app/Middleware',
        'app/Job',
        'app/Provider',
        'app/Api',
        'app/Migration',
        'app/Command',
        'app/lang/en',
        'app/view',
        'public/uploads',
        'runtime/twig',
        'runtime/log',
        'runtime/cache',
    ];

    public function handle(array $args): int
    {
        $projectName = $args[0] ?? null;
        $inPlace = ($projectName === '--from-create-project');

        if (!$projectName && !$inPlace) {
            $this->error('Usage: php ylmz new <project-name>');
            $this->line('Example: php ylmz new my-blog');
            return 1;
        }

        $this->sourceRoot = YL_ROOT;
        $this->targetRoot = $inPlace ? getcwd() : (getcwd() . '/' . $projectName);

        if (!$inPlace && file_exists($this->targetRoot)) {
            $this->error("Directory '{$projectName}' already exists.");
            return 1;
        }

        $displayName = $inPlace ? basename(getcwd()) : $projectName;
        $this->info("Creating Ylmz project: {$displayName}");
        $this->line('');

        // Step 1: Create directory
        $this->line('  [1/5] Creating project structure...');
        $this->createStructure();

        // Step 2: Copy framework core
        $this->line('  [2/5] Installing framework core...');
        $this->copyCoreFiles();
        foreach ($this->copyDirs as $src => $dst) {
            $this->copyDirectory(
                $this->sourceRoot . '/' . $src,
                $this->targetRoot . '/' . $dst
            );
        }

        // Step 3: Scaffold app (inline generation — no kernel dependency)
        $this->line('  [3/5] Scaffolding application...');
        $this->scaffoldApp();

        // Step 4: Install dependencies
        $this->line('  [4/5] Installing Composer dependencies...');
        chmod($this->targetRoot . '/ylmz', 0755);
        $this->runComposerInstall();

        // Step 5: Finalize
        $this->line('  [5/5] Finalizing...');
        $this->finalize();

        $this->line('');
        $this->info("✓ Project '{$displayName}' created successfully!");
        $this->line('');
        if (!$inPlace) {
            $this->line("  Get started:");
            $this->line("    cd {$projectName}");
            $this->line('    php ylmz serve');
            $this->line('');
        }
        $this->line('  Generate code:');
        $this->line('    php ylmz make:controller User');
        $this->line('    php ylmz make:model Post');
        $this->line('    php ylmz make:job SendEmail');
        $this->line('');

        return 0;
    }

    private function createStructure(): void
    {
        mkdir($this->targetRoot, 0755, true);

        foreach ($this->emptyDirs as $dir) {
            @mkdir($this->targetRoot . '/' . $dir, 0755, true);
        }

        // Placeholder .gitignore files
        $this->put('public/.gitignore', "*\n!.gitignore\n");
        $this->put('runtime/.gitignore', "*\n!.gitignore\n");
        $this->put('app/.gitignore', "*\n!.gitignore\n");
    }

    private function scaffoldApp(): void
    {
        // Entry point
        $this->put('index.php', <<<'PHP'
<?php

if (PHP_SAPI === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $public = __DIR__ . '/public';
    if ($uri !== '/' && file_exists($public . $uri)) {
        return false;
    }
}

require __DIR__ . '/core/run.php';
PHP);

        // Default controller
        $this->put('app/Ctrl/IndexCtrl.php', <<<'PHP'
<?php

namespace App\Ctrl;

use Ylmz\Controller;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class IndexCtrl extends Controller
{
    public function index(Request $request): Response
    {
        return $this->display('index.html');
    }
}
PHP);

        // Welcome page
        $this->put('app/view/index.html', <<<'HTML'
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ylmz Framework</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f5f5f5}
        .card{background:#fff;padding:48px 64px;border-radius:12px;box-shadow:0 2px 20px rgba(0,0,0,.08);text-align:center}
        h1{font-size:28px;color:#1a1a1a;margin-bottom:8px}
        p{color:#666;font-size:14px}
        .ver{display:inline-block;margin-top:16px;padding:4px 12px;background:#e8f4e8;color:#2a7a2a;border-radius:20px;font-size:12px}
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 Ylmz Framework</h1>
        <p>PHP MVC · MySQL · Redis · Queue</p>
        <span class="ver">PHP 8.0+</span>
    </div>
</body>
</html>
HTML);

        // Language file
        $this->put('app/lang/en/messages.php', <<<'PHP'
<?php

return [
    'welcome' => 'Welcome to Ylmz Framework',
    'error' => [
        '404' => 'Page not found',
        '500' => 'Internal server error',
    ],
    'auth' => [
        'failed' => 'These credentials do not match our records.',
        'success' => 'Logged in successfully.',
    ],
    'validation' => [
        'required' => 'The :field field is required.',
        'email' => 'The :field must be a valid email address.',
    ],
];
PHP);

        // Schedule file
        $this->put('app/schedule.php', <<<'PHP'
<?php

/**
 * Define scheduled tasks here.
 * Run with: php ylmz schedule:run
 * Cron: * * * * * cd /path/to/project && php ylmz schedule:run >> /dev/null 2>&1
 */

// Example:
// schedule()->command('php ylmz queue:work')->everyMinute();
// schedule()->call(function () {
//     \Ylmz\Log::info('Scheduled task ran!');
// })->daily();
PHP);
    }

    private function finalize(): void
    {
        copy(
            $this->targetRoot . '/.env.example',
            $this->targetRoot . '/.env'
        );
    }

    private function put(string $path, string $content): void
    {
        $fullPath = $this->targetRoot . '/' . $path;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($fullPath, $content);
    }

    private function copyCoreFiles(): void
    {
        foreach ($this->coreFiles as $file) {
            $src = $this->sourceRoot . '/' . $file;
            $dst = $this->targetRoot . '/' . $file;
            if (file_exists($src)) {
                $dir = dirname($dst);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                copy($src, $dst);
            }
        }
    }

    private function copyDirectory(string $src, string $dst): void
    {
        if (!is_dir($src)) {
            return;
        }

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $dst . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755);
                }
            } else {
                copy($item, $target);
            }
        }
    }

    private function runComposerInstall(): void
    {
        $composerJson = $this->targetRoot . '/composer.json';

        $original = file_get_contents($composerJson);
        $json = json_decode($original, true);
        unset($json['bin']);
        file_put_contents($composerJson, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $cwd = getcwd();
        chdir($this->targetRoot);

        $output = [];
        $exitCode = 0;
        exec('composer install --no-interaction 2>&1', $output, $exitCode);

        chdir($cwd);

        file_put_contents($composerJson, $original);

        if ($exitCode !== 0) {
            $this->warn('  ⚠ Run "composer install" manually if needed.');
        }
    }
}
