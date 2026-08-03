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
\Ylmz\Config::load(ROOT_PATH);

// Define debug constant
define('APP_DEBUG', \Ylmz\Config::getBool('APP_DEBUG', false));

// Load helper functions
require CORE_PATH . '/common/function.php';

// Initialize debug
\Ylmz\Debug::init();

// Initialize log
\Ylmz\Log::init();

// Initialize Redis (if configured and extension loaded)
if (\Ylmz\Redis::isAvailable()) {
    \Ylmz\Redis::setConfig([
        'default' => [
            'host' => \Ylmz\Config::get('REDIS_HOST', '127.0.0.1'),
            'port' => \Ylmz\Config::getInt('REDIS_PORT', 6379),
            'password' => \Ylmz\Config::get('REDIS_PASSWORD'),
            'database' => \Ylmz\Config::getInt('REDIS_DATABASE', 0),
            'prefix' => \Ylmz\Config::get('REDIS_PREFIX', 'ylmz:'),
        ],
    ]);
}

// Create and boot application
$app = \Ylmz\Application::getInstance();

// Register service providers
if (file_exists(APP_PATH . '/Provider/RouteServiceProvider.php')) {
    $app->register(\App\Provider\RouteServiceProvider::class);
}

// Run the application
$app->run();
