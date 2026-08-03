<?php

namespace Ylmz\Support;

use Ylmz\Foundation\Config;

class Crypt
{
    private static string $cipher = 'aes-256-cbc';

    /**
     * Encrypt a value.
     */
    public static function encrypt(mixed $value): string
    {
        $key = self::key();
        $iv = random_bytes(openssl_cipher_iv_length(self::$cipher));
        $encrypted = openssl_encrypt(
            serialize($value),
            self::$cipher,
            $key,
            0,
            $iv
        );

        $mac = hash_hmac('sha256', $iv . $encrypted, $key);
        return base64_encode(json_encode(['iv' => base64_encode($iv), 'value' => $encrypted, 'mac' => $mac]));
    }

    /**
     * Decrypt a value.
     */
    public static function decrypt(string $payload): mixed
    {
        $key = self::key();
        $data = json_decode(base64_decode($payload), true);

        if (!isset($data['iv'], $data['value'], $data['mac'])) {
            throw new \RuntimeException('Invalid payload.');
        }

        $iv = base64_decode($data['iv']);
        $mac = hash_hmac('sha256', $iv . $data['value'], $key);

        if (!hash_equals($mac, $data['mac'])) {
            throw new \RuntimeException('MAC verification failed.');
        }

        return unserialize(openssl_decrypt($data['value'], self::$cipher, $key, 0, $iv));
    }

    /**
     * Generate a random hex string.
     */
    public static function random(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Get the encryption key.
     */
    private static function key(): string
    {
        $key = Config::get('APP_KEY', '');
        if (empty($key)) {
            throw new \RuntimeException('APP_KEY not set in .env. Run: php ylmz key:generate');
        }

        $key = substr($key, 0, 32);
        if (strlen($key) < 32) {
            $key = str_pad($key, 32, '0');
        }
        return $key;
    }
}
