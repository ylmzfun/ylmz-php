<?php

namespace Ylmz\Support;

use Ylmz\Foundation\Config;
use Ylmz\Model;
use Ylmz\Support\Hash;

class Auth
{
    /**
     * Attempt login with username/email and password.
     * Returns JWT token on success, null on failure.
     */
    public static function attempt(array $credentials): ?string
    {
        $model = $credentials['model'] ?? 'App\\Model\\User';
        $table = $credentials['table'] ?? 'users';
        $userField = $credentials['user_field'] ?? 'email';
        $passField = $credentials['pass_field'] ?? 'password';
        $username = $credentials[$userField] ?? '';
        $password = $credentials[$passField] ?? '';

        $user = Model::db()->get($table, '*', [$userField => $username]);

        if (!$user || !Hash::check($password, $user[$passField])) {
            return null;
        }

        return self::createToken($user, $credentials['secret'] ?? Config::get('APP_KEY'));
    }

    /**
     * Create a JWT token for a user.
     */
    public static function createToken(array $user, string $secret): string
    {
        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $payload = self::base64UrlEncode(json_encode([
            'sub' => (string)($user['id'] ?? '0'),
            'iat' => time(),
            'exp' => time() + 86400 * 7, // 7 days
            'data' => [
                'id' => $user['id'] ?? null,
                'email' => $user['email'] ?? null,
            ],
        ]));

        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $secret, true)
        );

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * Verify and decode a JWT token.
     */
    public static function verify(string $token, ?string $secret = null): ?array
    {
        $secret = $secret ?: Config::get('APP_KEY', '');
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        $expected = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", $secret, true)
        );

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $data = json_decode(self::base64UrlDecode($payload), true);

        if (!$data || ($data['exp'] ?? 0) < time()) {
            return null;
        }

        return $data['data'] ?? null;
    }

    /**
     * Get the current authenticated user from request.
     */
    public static function user(): ?array
    {
        $token = self::getBearerToken();
        if (!$token) {
            return null;
        }

        return self::verify($token);
    }

    /**
     * Check if a user is authenticated.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    /**
     * Get user ID from token.
     */
    public static function id(): ?int
    {
        $user = self::user();
        return isset($user['id']) ? (int)$user['id'] : null;
    }

    private static function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $header, $m)) {
            return $m[1];
        }
        return null;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
