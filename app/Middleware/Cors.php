<?php

namespace App\Middleware;

use Closure;
use Ylmz\Http\Middleware;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class Cors implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

        if ($request->method() === 'OPTIONS') {
            $response->setStatusCode(204);
            $response->setContent('');
        }

        return $response;
    }
}
