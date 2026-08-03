<?php

/**
 * Ylmz PHP Framework
 * Bootstrap file
 */

// Define root path
define('ROOT_PATH', realpath(__DIR__ . '/..'));

// Define core path
define('CORE_PATH', ROOT_PATH . '/core');

// Define app path
define('APP_PATH', ROOT_PATH . '/app');

// Define runtime path
define('RUNTIME_PATH', ROOT_PATH . '/runtime');

// Load composer autoload
require ROOT_PATH . '/vendor/autoload.php';

// Load environment config
\Ylmz\Foundation\Config::load(ROOT_PATH);

// Define debug constant
define('APP_DEBUG', \Ylmz\Foundation\Config::getBool('APP_DEBUG', false));

// Auto-generate APP_KEY if empty
if (empty(\Ylmz\Foundation\Config::get('APP_KEY', ''))) {
    $key = 'base64:' . base64_encode(random_bytes(32));
    \Ylmz\Foundation\Config::set('APP_KEY', $key);
}

// Load helper functions
require CORE_PATH . '/common/function.php';

// Initialize debug
\Ylmz\Support\Debug::init();

// Initialize session (CSRF token auto-generated)
\Ylmz\Support\Session::start();

// Initialize log
\Ylmz\Log::init();

// Initialize Redis (if configured and extension loaded)
if (\Ylmz\Support\Redis::isAvailable()) {
    \Ylmz\Support\Redis::setConfig([
        'default' => [
            'host' => \Ylmz\Foundation\Config::get('REDIS_HOST', '127.0.0.1'),
            'port' => \Ylmz\Foundation\Config::getInt('REDIS_PORT', 6379),
            'password' => \Ylmz\Foundation\Config::get('REDIS_PASSWORD'),
            'database' => \Ylmz\Foundation\Config::getInt('REDIS_DATABASE', 0),
            'prefix' => \Ylmz\Foundation\Config::get('REDIS_PREFIX', 'ylmz:'),
        ],
    ]);
}

// Create and boot application
$app = \Ylmz\Foundation\Application::getInstance();

// Register service providers
if (file_exists(APP_PATH . '/Provider/RouteServiceProvider.php')) {
    $app->register(\App\Provider\RouteServiceProvider::class);
}

// Run the application
$app->run();
