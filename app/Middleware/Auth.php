<?php

namespace App\Middleware;

use Closure;
use Ylmz\Http\Middleware;
use Ylmz\Http\Request;
use Ylmz\Http\Response;

class Auth implements Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated (example: session/cookie check)
        // if (!isset($_SESSION['user_id'])) {
        //     $response = new Response();
        //     return $response->redirect('/login');
        // }

        return $next($request);
    }
}
