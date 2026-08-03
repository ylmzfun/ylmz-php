<?php

namespace Ylmz\Support;

class Event
{
    private static array $listeners = [];

    /**
     * Register a listener for an event.
     */
    public static function listen(string $event, callable|string $listener): void
    {
        self::$listeners[$event][] = $listener;
    }

    /**
     * Dispatch an event, calling all registered listeners.
     */
    public static function dispatch(string $event, mixed $payload = null): void
    {
        if (!isset(self::$listeners[$event])) {
            return;
        }

        foreach (self::$listeners[$event] as $listener) {
            if (is_string($listener) && class_exists($listener)) {
                (new $listener())->handle($payload);
            } else {
                $listener($payload);
            }
        }
    }

    /**
     * Check if event has listeners.
     */
    public static function hasListeners(string $event): bool
    {
        return !empty(self::$listeners[$event]);
    }

    /**
     * Remove all listeners for an event.
     */
    public static function forget(string $event): void
    {
        unset(self::$listeners[$event]);
    }
}
