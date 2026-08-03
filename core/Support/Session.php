<?php

namespace Ylmz\Support;

class Session
{
    private static bool $started = false;

    /**
     * Start session (if not already started).
     */
    public static function start(): void
    {
        if (self::$started || PHP_SAPI === 'cli') {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // CSRF token initialization
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }

        self::$started = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::set('_flash_' . $key, $value);
    }

    public static function getFlash(string $key): mixed
    {
        $value = self::get('_flash_' . $key);
        self::delete('_flash_' . $key);
        return $value;
    }

    public static function csrfToken(): string
    {
        self::start();
        return $_SESSION['_csrf_token'];
    }

    public static function csrfField(): string
    {
        $token = htmlspecialchars(self::csrfToken());
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }

    public static function destroy(): void
    {
        self::start();
        session_destroy();
        self::$started = false;
    }

    /**
     * Regenerate session ID (prevents fixation).
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }
}
