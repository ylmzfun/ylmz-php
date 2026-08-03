<?php

namespace App\Provider;

use Ylmz\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register route-related bindings if needed
    }

    public function boot(): void
    {
        $router = \Ylmz\Application::getInstance()->getRouter();

        // Example: register explicit routes with middleware
        // $router->get('/api/users', [\App\Ctrl\UserCtrl::class, 'index'], [
        //     \App\Middleware\Auth::class,
        // ]);

        // $router->group([\App\Middleware\Cors::class], function ($router) {
        //     $router->get('/api/posts', [\App\Ctrl\PostCtrl::class, 'index']);
        //     $router->post('/api/posts', [\App\Ctrl\PostCtrl::class, 'store']);
        // });
    }
}
