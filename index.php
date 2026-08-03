<?php

/**
 * Ylmz PHP Framework - Entry Point
 *
 * Works as:
 *   1. Apache/Nginx entry (via .htaccess rewrite)
 *   2. PHP built-in server router (php -S)
 */

// Built-in server: serve static files from public/
if (PHP_SAPI === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $public = __DIR__ . '/public';
    if ($uri !== '/' && file_exists($public . $uri)) {
        return false;
    }
}

require __DIR__ . '/core/Run.php';
