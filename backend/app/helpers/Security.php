<?php

class Security
{
    private const CIPHER = 'aes-256-cbc';

    public static function encrypt(string $plaintext): string
    {
        $key = self::getKey();
        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        $encrypted = openssl_encrypt($plaintext, self::CIPHER, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt(string $encoded): string
    {
        $key = self::getKey();
        $data = base64_decode($encoded);
        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($data, 0, $ivLen);
        $encrypted = substr($data, $ivLen);
        return openssl_decrypt($encrypted, self::CIPHER, $key, 0, $iv);
    }

    private static function getKey(): string
    {
        $raw = JWT_SECRET;
        return hash('sha256', $raw, true);
    }
}
