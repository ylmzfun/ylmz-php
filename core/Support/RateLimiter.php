<?php

namespace Ylmz\Support;

class RateLimiter
{
    private static array $attempts = [];

    /**
     * Check if a key has exceeded the max attempts within decay minutes.
     */
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decayMinutes = 1): bool
    {
        $hits = self::hits($key, $decayMinutes);
        return $hits >= $maxAttempts;
    }

    /**
     * Record a hit for the given key.
     */
    public static function hit(string $key, int $decayMinutes = 1): int
    {
        $redisKey = 'rate_limit:' . $key;
        $now = time();
        $window = $now - ($decayMinutes * 60);

        if (Redis::isAvailable()) {
            $redis = Redis::connection();
            $redis->zRemRangeByScore($redisKey, 0, $window);
            $redis->zAdd($redisKey, $now, $now . '.' . uniqid());
            $redis->expire($redisKey, $decayMinutes * 60 + 10);
            return (int)$redis->zCard($redisKey);
        }

        // Fallback: in-memory
        if (!isset(self::$attempts[$key])) {
            self::$attempts[$key] = [];
        }
        self::$attempts[$key] = array_filter(
            self::$attempts[$key],
            fn($t) => $t > $window
        );
        self::$attempts[$key][] = $now;
        return count(self::$attempts[$key]);
    }

    /**
     * Get the number of attempts for the given key.
     */
    public static function hits(string $key, int $decayMinutes = 1): int
    {
        $redisKey = 'rate_limit:' . $key;

        if (Redis::isAvailable()) {
            $window = time() - ($decayMinutes * 60);
            $redis = Redis::connection();
            $redis->zRemRangeByScore($redisKey, 0, $window);
            return (int)$redis->zCard($redisKey);
        }

        $window = time() - ($decayMinutes * 60);
        self::$attempts[$key] = array_filter(
            self::$attempts[$key] ?? [],
            fn($t) => $t > $window
        );
        return count(self::$attempts[$key]);
    }

    /**
     * Get remaining attempts.
     */
    public static function remaining(string $key, int $maxAttempts): int
    {
        return max(0, $maxAttempts - self::hits($key));
    }

    /**
     * Clear hits for the given key.
     */
    public static function clear(string $key): void
    {
        if (Redis::isAvailable()) {
            Redis::connection()->del('rate_limit:' . $key);
        }
        unset(self::$attempts[$key]);
    }
}
