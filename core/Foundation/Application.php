<?php

namespace Ylmz\Foundation;

use Ylmz\Router;
use Ylmz\Log;
use Ylmz\Http\Request;
use Ylmz\Http\Response;
use Throwable;

class Application
{
    private static ?self $instance = null;
    private Container $container;
    private Router $router;
    private array $providers = [];
    private bool $booted = false;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->router = new Router($this->container);

        $this->container->instance(Container::class, $this->container);
        $this->container->instance(Application::class, $this);
        $this->container->singleton(Request::class);
        $this->container->singleton(Response::class);
        $this->container->singleton(ExceptionHandler::class);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Register a service provider
     */
    public function register(ServiceProvider|string $provider): void
    {
        if (is_string($provider)) {
            $provider = new $provider($this->container);
        }
        $provider->register();
        $this->providers[] = $provider;
    }

    /**
     * Boot all registered providers
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }

    /**
     * Run the application (handle HTTP request)
     */
    public function run(): void
    {
        try {
            $this->boot();
            $request = $this->container->make(Request::class);
            $response = $this->router->dispatch($request);
            $response->send();
            Log::info('Request: ' . $request->method() . ' ' . $request->path());

        } catch (Throwable $e) {
            /** @var ExceptionHandler $handler */
            $handler = $this->container->make(ExceptionHandler::class);
            $response = $handler->handle($e);
            $response->send();
        }
    }
}
