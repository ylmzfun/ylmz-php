<?php

namespace Ylmz\Support;

class HttpClient
{
    private string $baseUrl = '';
    private array $headers = [];
    private int $timeout = 30;
    private array $options = [];

    public static function new(): self { return new self(); }

    public function baseUrl(string $url): self  { $this->baseUrl = rtrim($url, '/'); return $this; }
    public function timeout(int $seconds): self   { $this->timeout = $seconds; return $this; }
    public function withHeaders(array $headers): self { $this->headers = array_merge($this->headers, $headers); return $this; }
    public function withToken(string $token): self   { return $this->withHeaders(['Authorization' => 'Bearer ' . $token]); }

    public function get(string $url, array $query = []): Response
    {
        return $this->request('GET', $url, $query);
    }

    public function post(string $url, array $data = []): Response
    {
        return $this->request('POST', $url, [], $data);
    }

    public function put(string $url, array $data = []): Response
    {
        return $this->request('PUT', $url, [], $data);
    }

    public function delete(string $url): Response
    {
        return $this->request('DELETE', $url);
    }

    private function request(string $method, string $url, array $query = [], ?array $body = null): Response
    {
        $url = $this->baseUrl ? $this->baseUrl . '/' . ltrim($url, '/') : $url;

        if ($query) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HEADER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->formatHeaders(),
        ] + $this->options);

        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        }

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("HTTP request failed: {$error}");
        }

        $response = new Http\Response();
        $response->setStatusCode($statusCode);
        $response->setContent(substr($raw, $headerSize));

        return $response;
    }

    private function formatHeaders(): array
    {
        $formatted = [];
        foreach ($this->headers as $k => $v) {
            $formatted[] = "{$k}: {$v}";
        }
        return $formatted;
    }
}
