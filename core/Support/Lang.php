<?php

namespace Ylmz\Support;

class Lang
{
    private static array $translations = [];
    private static string $locale = 'en';

    /**
     * Set the current locale.
     */
    public static function setLocale(string $locale): void
    {
        self::$locale = $locale;
    }

    /**
     * Get the current locale.
     */
    public static function getLocale(): string
    {
        return self::$locale;
    }

    /**
     * Load translation files for a locale.
     */
    public static function load(string $locale): void
    {
        $path = APP_PATH . '/lang/' . $locale;
        if (!is_dir($path)) {
            return;
        }

        foreach (glob($path . '/*.php') as $file) {
            $group = basename($file, '.php');
            $lines = require $file;
            self::$translations[$locale][$group] = $lines;
        }
    }

    /**
     * Get a translation string. Supports dot notation: trans('messages.welcome')
     */
    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? self::$locale;

        if (!isset(self::$translations[$locale])) {
            self::load($locale);
        }

        $value = self::getValue($key, $locale) ?: $key;

        foreach ($replace as $search => $replace) {
            $value = str_replace(':' . $search, (string)$replace, $value);
        }

        return $value;
    }

    /**
     * Get translation with pluralization.
     */
    public static function choice(string $key, int $count, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? self::$locale;

        if (!isset(self::$translations[$locale])) {
            self::load($locale);
        }

        $value = self::getValue($key, $locale);

        if (!$value) {
            return $key;
        }

        // Handle plural forms like 'apples|apple|apples'
        if (str_contains($value, '|')) {
            $options = explode('|', $value);
            if ($count === 1 && isset($options[1])) {
                $value = $options[1];
            } elseif (isset($options[0])) {
                $value = $options[0];
            }
        }

        $replace['count'] = $count;

        foreach ($replace as $search => $val) {
            $value = str_replace(':' . $search, (string)$val, $value);
        }

        return $value;
    }

    private static function getValue(string $key, string $locale): ?string
    {
        $segments = explode('.', $key);
        $group = array_shift($segments);
        $lines = self::$translations[$locale][$group] ?? [];

        foreach ($segments as $segment) {
            if (!is_array($lines) || !array_key_exists($segment, $lines)) {
                return null;
            }
            $lines = $lines[$segment];
        }

        return is_string($lines) ? $lines : null;
    }
}
