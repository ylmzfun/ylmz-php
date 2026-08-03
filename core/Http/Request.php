<?php

namespace Ylmz\Http;

class Request
{
    private array $query;
    private array $body;
    private array $server;
    private array $files;
    private array $cookies;
    private array $headers;
    private string $method;
    private string $path;
    private string $url;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;

        $this->method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->path = '/' . trim($uri, '/');
        $this->url = $this->server['REQUEST_URI'] ?? '/';

        $this->headers = $this->parseHeaders();
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->input($key);
        }
        return $data;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, ?string $default = null): ?string
    {
        return $this->headers[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function isJson(): bool
    {
        return str_contains($this->header('Content-Type') ?? '', 'application/json');
    }

    public function getJsonBody(): array
    {
        if (!$this->isJson()) {
            return [];
        }
        $content = file_get_contents('php://input');
        return json_decode($content, true) ?: [];
    }
}
