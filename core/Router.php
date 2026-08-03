<?php

namespace Ylmz;

use Ylmz\Http\Request;
use Ylmz\Http\Response;
use Ylmz\Http\Middleware;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private array $groupMiddlewares = [];
    private string $defaultController = 'index';
    private string $defaultMethod = 'index';

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function __construct(
        private Container $container
    ) {}

    public function get(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    public function any(string $path, array|callable $handler, array $middlewares = []): self
    {
        return $this->addRoute('ANY', $path, $handler, $middlewares);
    }

    public function group(array $middlewares, Closure $callback): void
    {
        $previous = $this->groupMiddlewares;
        $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);
        $callback($this);
        $this->groupMiddlewares = $previous;
    }

    public function middleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => '/' . trim($path, '/'),
            'handler' => $handler,
            'middlewares' => array_merge($this->groupMiddlewares, $this->middlewares, $middlewares),
        ];
        $this->middlewares = [];
        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $path = $request->path() ?: '/';
        $method = $request->method();

        foreach ($this->routes as $route) {
            $params = $this->matchRoute($route['path'], $path);
            if ($params !== false && ($route['method'] === 'ANY' || $route['method'] === $method)) {
                return $this->runRoute($route, $request, $params);
            }
        }

        return $this->dispatchAuto($request);
    }

    private function matchRoute(string $routePath, string $requestPath): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[trim($part, '{}')] = $requestParts[$i];
            } elseif ($part !== $requestParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    private function runRoute(array $route, Request $request, array $params): Response
    {
        $coreHandler = function (Request $request) use ($route, $params) {
            $request->params = $params;

            if (is_callable($route['handler'])) {
                return $route['handler']($request);
            }

            [$controller, $method] = $route['handler'];
            $instance = $this->container->make($controller);
            return $instance->$method($request);
        };

        $handler = $this->wrapMiddlewares($route['middlewares'], $coreHandler);

        return $handler($request);
    }

    private function wrapMiddlewares(array $middlewares, Closure $core): Closure
    {
        foreach (array_reverse($middlewares) as $mw) {
            $instance = $this->container->make($mw);
            $next = $core;
            $core = fn(Request $request): Response => $instance->handle($request, $next);
        }
        return $core;
    }

    private function dispatchAuto(Request $request): Response
    {
        $path = trim($request->path(), '/');
        $parts = $path ? explode('/', $path) : [];

        $controllerName = $parts[0] ?? $this->defaultController;
        $methodName = $parts[1] ?? $this->defaultMethod;

        // Extra segments become query params: /ctrl/method/k1/v1/k2/v2
        $count = count($parts);
        for ($i = 2; $i + 1 < $count; $i += 2) {
            $_GET[$parts[$i]] = $parts[$i + 1];
        }

        $controllerClass = 'App\\Ctrl\\' . ucfirst($controllerName) . 'Ctrl';

        if (!class_exists($controllerClass)) {
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('Controller not found: ' . $controllerName);
            return $response;
        }

        $instance = $this->container->make($controllerClass);

        if (!method_exists($instance, $methodName)) {
            $response = new Response();
            $response->setStatusCode(404);
            $response->setContent('Method not found: ' . $methodName);
            return $response;
        }

        return $instance->$methodName($request);
    }
}
