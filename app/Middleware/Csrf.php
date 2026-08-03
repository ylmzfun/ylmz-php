<?php

namespace App\Middleware;

use Closure;
use Ylmz\Http\Middleware;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class Csrf implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $request->post('_token', $request->header('X-CSRF-Token'));
            $sessionToken = $_SESSION['_csrf_token'] ?? null;

            if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
                $response = new Response();
                return $response->setStatusCode(419)->setContent('<h1>419 Page Expired</h1>');
            }
        }

        return $next($request);
    }
}
