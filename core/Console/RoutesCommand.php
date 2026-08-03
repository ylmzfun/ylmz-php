<?php

namespace Ylmz\Console;

class RoutesCommand extends Command
{
    protected string $signature = 'routes';
    protected string $description = 'Show all registered routes';

    public function handle(array $args): int
    {
        $router = \Ylmz\Foundation\Application::getInstance()->getRouter();
        $routes = $router->getRoutes();

        if (empty($routes)) {
            $this->warn('No routes registered. Using auto-routing.');
            return 0;
        }

        $this->info('Registered Routes:');
        $this->line(str_repeat('-', 60));
        $this->line(sprintf('  %-8s %-30s %s', 'METHOD', 'PATH', 'HANDLER'));

        foreach ($routes as $route) {
            $method = $route['method'];
            $path = $route['path'];
            $handler = is_array($route['handler'])
                ? $route['handler'][0] . '::' . $route['handler'][1]
                : 'Closure';

            $this->line(sprintf('  %-8s %-30s %s', $method, $path, $handler));
        }

        return 0;
    }
}
