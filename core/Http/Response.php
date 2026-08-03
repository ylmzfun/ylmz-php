<?php

namespace Ylmz\Http;

class Response
{
    private string $content = '';
    private int $statusCode = 200;
    private array $headers = [];
    private array $viewData = [];

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json(array $data, int $code = 200): self
    {
        $this->statusCode = $code;
        $this->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this;
    }

    public function redirect(string $url, int $code = 302): self
    {
        $this->statusCode = $code;
        $this->setHeader('Location', $url);
        return $this;
    }

    public function withViewData(string $key, mixed $value): self
    {
        $this->viewData[$key] = $value;
        return $this;
    }

    public function getViewData(): array
    {
        return $this->viewData;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        echo $this->content;
    }
}
