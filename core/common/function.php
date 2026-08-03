<?php

if (!function_exists('p')) {
    function p(mixed $var): void
    {
        if (is_bool($var)) {
            var_dump($var);
        } elseif (is_null($var)) {
            var_dump(null);
        } else {
            print_r($var);
        }
    }
}

if (!function_exists('app')) {
    function app(): \Ylmz\Application
    {
        return \Ylmz\Application::getInstance();
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return \Ylmz\Config::get($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        $loader = new \Twig\Loader\FilesystemLoader(APP_PATH . '/view/');
        $twig = new \Twig\Environment($loader, [
            'cache' => RUNTIME_PATH . '/twig/',
            'debug' => \Ylmz\Config::getBool('APP_DEBUG', false),
        ]);
        return $twig->render($template, $data);
    }
}
