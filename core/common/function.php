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
    function app(): \Ylmz\Foundation\Application
    {
        return \Ylmz\Foundation\Application::getInstance();
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return \Ylmz\Foundation\Config::get($key, $default);
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        $loader = new \Twig\Loader\FilesystemLoader(APP_PATH . '/view/');
        $twig = new \Twig\Environment($loader, [
            'cache' => RUNTIME_PATH . '/twig/',
            'debug' => \Ylmz\Foundation\Config::getBool('APP_DEBUG', false),
        ]);
        return $twig->render($template, $data);
    }
}

if (!function_exists('collect')) {
    function collect(array $items = []): \Ylmz\Support\Collection
    {
        return \Ylmz\Support\Collection::make($items);
    }
}

if (!function_exists('encrypt')) {
    function encrypt(mixed $value): string { return \Ylmz\Support\Crypt::encrypt($value); }
}

if (!function_exists('decrypt')) {
    function decrypt(string $payload): mixed { return \Ylmz\Support\Crypt::decrypt($payload); }
}

if (!function_exists('bcrypt')) {
    function bcrypt(string $value): string { return \Ylmz\Support\Hash::make($value); }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string { return \Ylmz\Support\Session::csrfToken(); }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string { return \Ylmz\Support\Session::csrfField(); }
}

if (!function_exists('session')) {
    function session(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return \Ylmz\Support\Session::class;
        return \Ylmz\Support\Session::get($key, $default);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        return \Ylmz\Support\Session::getFlash('_old_input.' . $key) ?? $default;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $code = 302): \Ylmz\Http\Response
    {
        return (new \Ylmz\Http\Response())->redirect($url, $code);
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): void
    {
        $handler = new \Ylmz\Foundation\ExceptionHandler();
        $handler->renderHttp($code, $message)->send();
        exit;
    }
}

if (!function_exists('route')) {
    function route(string $name): string
    {
        $routes = \Ylmz\Foundation\Application::getInstance()->getRouter()->getRoutes();
        foreach ($routes as $r) {
            if (($r['name'] ?? '') === $name) return $r['path'];
        }
        throw new \RuntimeException("Route [{$name}] not defined.");
    }
}

if (!function_exists('event')) {
    function event(string $name, mixed $payload = null): void
    {
        \Ylmz\Support\Event::dispatch($name, $payload);
    }
}

if (!function_exists('rate_limiter')) {
    function rate_limiter(): \Ylmz\Support\RateLimiter
    {
        return new \Ylmz\Support\RateLimiter();
    }
}

if (!function_exists('str')) {
    function str(): \Ylmz\Support\Str { return new \Ylmz\Support\Str(); }
}

if (!function_exists('now')) {
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }
}

if (!function_exists('trans')) {
    function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        return \Ylmz\Support\Lang::get($key, $replace, $locale);
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return \Ylmz\Support\Lang::get($key, $replace, $locale);
    }
}

if (!function_exists('trans_choice')) {
    function trans_choice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        return \Ylmz\Support\Lang::choice($key, $count, $replace, $locale);
    }
}

if (!function_exists('xss')) {
    function xss(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('schedule')) {
    function schedule(): \Ylmz\Support\Schedule
    {
        static $schedule = null;
        if ($schedule === null) {
            $schedule = new \Ylmz\Support\Schedule();
        }
        return $schedule;
    }
}
