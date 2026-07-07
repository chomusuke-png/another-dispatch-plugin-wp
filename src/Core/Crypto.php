<?php

declare(strict_types=1);

namespace Zumito\ADP\Core;

class Crypto
{
    private const CIPHER = 'aes-256-cbc';
    private const ENC_PREFIX = 'adp_enc::';

    public static function encrypt(string $plaintext): string
    {
        if ($plaintext === '') {
            return '';
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, self::getEncryptionKey(), OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            return $plaintext;
        }

        return self::ENC_PREFIX . base64_encode($iv . $ciphertext);
    }

    public static function decrypt(string $storedValue): string
    {
        if ($storedValue === '' || strpos($storedValue, self::ENC_PREFIX) !== 0) {
            return $storedValue;
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $decoded = base64_decode(substr($storedValue, strlen(self::ENC_PREFIX)), true);

        if ($decoded === false || strlen($decoded) <= $ivLength) {
            return '';
        }

        $iv = substr($decoded, 0, $ivLength);
        $ciphertext = substr($decoded, $ivLength);
        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, self::getEncryptionKey(), OPENSSL_RAW_DATA, $iv);

        return $plaintext !== false ? $plaintext : '';
    }

    public static function sign(string $value): string
    {
        return hash_hmac('sha256', $value, self::getSigningKey());
    }

    public static function verify(string $value, string $signature): bool
    {
        if ($signature === '') {
            return false;
        }

        return hash_equals(self::sign($value), $signature);
    }

    private static function getEncryptionKey(): string
    {
        return hash('sha256', wp_salt('auth'), true);
    }

    private static function getSigningKey(): string
    {
        return wp_salt('auth');
    }
}
