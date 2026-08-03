<?php

namespace Ylmz;

use Ylmz\Http\Request;
use Ylmz\Http\Response;
use Throwable;

class ExceptionHandler
{
    public function handle(Throwable $e): Response
    {
        Log::error($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        $response = new Response();

        if (Config::getBool('APP_DEBUG', false)) {
            $body = $this->renderDebug($e);
        } else {
            $response->setStatusCode(500);
            $body = '<h1>500 Internal Server Error</h1><p>Something went wrong.</p>';
        }

        $response->setContent($body);
        return $response;
    }

    public function renderHttp(int $code, string $message = ''): Response
    {
        $response = new Response();
        $response->setStatusCode($code);

        $templates = [
            404 => '<h1>404 Not Found</h1><p>The page you requested was not found.</p>',
            403 => '<h1>403 Forbidden</h1><p>You do not have permission to access this resource.</p>',
            405 => '<h1>405 Method Not Allowed</h1>',
            500 => '<h1>500 Internal Server Error</h1>',
        ];

        $body = $message ?: ($templates[$code] ?? "<h1>{$code} Error</h1>");
        $response->setContent($body);
        return $response;
    }

    private function renderDebug(Throwable $e): string
    {
        $type = get_class($e);
        $message = htmlspecialchars($e->getMessage());
        $file = htmlspecialchars($e->getFile());
        $line = $e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString());

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>{$type}</title>
<style>
body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px}
h1{color:#f44747} .file{color:#569cd6} .trace{color:#808080;font-size:12px;white-space:pre-wrap}
</style></head><body>
<h1>{$type}</h1><p>{$message}</p>
<p class="file">{$file}:{$line}</p>
<pre class="trace">{$trace}</pre>
</body></html>
HTML;
    }
}
