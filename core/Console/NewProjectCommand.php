<?php

namespace Ylmz\Console;

class NewProjectCommand extends Command
{
    protected string $signature = 'new <project-name>';
    protected string $description = 'Create a new Ylmz project';

    private string $sourceRoot;
    private string $targetRoot;

    /** Files to copy from framework to new project */
    private array $coreFiles = [
        'composer.json',
        '.env.example',
        '.gitignore',
        '.htaccess',
        'README.md',
        'ylmz',
        'router.php',
    ];

    /**
     * Directories that hold framework code — copied entirely from source
     * 'dir' => 'targetDir'
     */
    private array $copyDirs = [
        'core' => 'core',
    ];

    /**
     * Skeleton files: files from current installation to copy to new project.
     * These are the minimal app starter files.
     */
    private array $skeletonFiles = [
        'index.php',
        'app/.gitignore',
        'app/Ctrl/IndexCtrl.php',
        'app/view/index.html',
    ];

    /** Empty dirs to create */
    private array $emptyDirs = [
        'runtime',
        'runtime/twig',
        'runtime/log',
        'runtime/cache',
        'public/uploads',
        'app/Model',
        'app/Middleware',
        'app/Job',
        'app/Provider',
        'app/Api',
    ];

    /** Files with placeholder content */
    private array $createFiles = [
        'public/.gitignore' => "*\n!.gitignore\n",
        'runtime/.gitignore' => "*\n!.gitignore\n",
        '.env' => '', // created from .env.example
    ];

    public function handle(array $args): int
    {
        $projectName = $args[0] ?? null;

        if (!$projectName) {
            $this->error('Usage: php ylmz new <project-name>');
            $this->line('Example: php ylmz new my-blog');
            return 1;
        }

        $this->sourceRoot = YL_ROOT;
        $this->targetRoot = getcwd() . '/' . $projectName;

        // Validate
        if (file_exists($this->targetRoot)) {
            $this->error("Directory '{$projectName}' already exists.");
            return 1;
        }

        if (!is_writable(getcwd())) {
            $this->error('Current directory is not writable.');
            return 1;
        }

        // Start
        $this->info("Creating Ylmz project: {$projectName}");
        $this->line('');

        // Step 1: Create directory
        $this->line('  [1/5] Creating project directory...');
        mkdir($this->targetRoot, 0755, true);

        // Step 2: Copy core framework
        $this->line('  [2/5] Installing framework core...');
        $this->copyCoreFiles();
        foreach ($this->copyDirs as $src => $dst) {
            $this->copyDirectory(
                $this->sourceRoot . '/' . $src,
                $this->targetRoot . '/' . $dst
            );
        }

        // Step 3: Create app skeleton
        $this->line('  [3/5] Scaffolding application...');
        $this->scaffoldApp();

        // Step 4: Create empty dirs and placeholder files
        foreach ($this->emptyDirs as $dir) {
            $path = $this->targetRoot . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        foreach ($this->createFiles as $dest => $content) {
            $path = $this->targetRoot . '/' . $dest;
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($path, $content);
        }

        // Make ylmz executable
        chmod($this->targetRoot . '/ylmz', 0755);

        // Step 5: Install dependencies
        $this->line('  [4/5] Installing Composer dependencies...');
        $this->runComposerInstall();

        // Done
        $this->line('  [5/5] Finalizing...');

        // Copy .env from .env.example
        copy(
            $this->targetRoot . '/.env.example',
            $this->targetRoot . '/.env'
        );

        $this->line('');
        $this->info("✓ Project '{$projectName}' created successfully!");
        $this->line('');
        $this->line('  Next steps:');
        $this->line("    cd {$projectName}");
        $this->line('    php ylmz serve');
        $this->line('');
        $this->line('  Generate code:');
        $this->line('    php ylmz make:controller User');
        $this->line('    php ylmz make:model Post');
        $this->line('    php ylmz make:middleware ApiAuth');
        $this->line('');

        return 0;
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

    private function scaffoldApp(): void
    {
        // Copy skeleton files from the current installation
        foreach ($this->skeletonFiles as $file) {
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

        // Temporarily remove the "bin" entry to avoid issues during install
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

        // Restore composer.json with bin
        file_put_contents($composerJson, $original);

        if ($exitCode !== 0) {
            $this->warn('  ⚠ Composer install had warnings (you may need to run "composer install" manually)');
        }
    }
}
