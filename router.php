<?php

/**
 * Ylmz Framework - Built-in Server Router
 * Used by: php ylmz serve
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files from public/
$publicPath = __DIR__ . '/public';

if ($uri !== '/' && file_exists($publicPath . $uri)) {
    return false;
}

// Route everything else through the framework
require __DIR__ . '/index.php';
