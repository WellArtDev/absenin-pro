<?php

class Sanitizer
{
    public static function string(mixed $value, int $maxLength = 255): string
    {
        if ($value === null) return '';
        $value = trim(strip_tags((string) $value));
        return mb_substr($value, 0, $maxLength);
    }

    public static function int(mixed $value, int $default = 0): int
    {
        return filter_var($value, FILTER_VALIDATE_INT) ?: $default;
    }

    public static function float(mixed $value, float $default = 0.0): float
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) ?: $default;
    }

    public static function email(mixed $value): string
    {
        $value = self::string($value, 254);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
    }

    public static function date(mixed $value): string
    {
        $value = self::string($value, 10);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value)) {
            return $value;
        }
        return '';
    }

    public static function url(mixed $value): string
    {
        $value = self::string($value, 2048);
        return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
    }

    public static function uuid(mixed $value): string
    {
        $value = self::string($value, 36);
        if (preg_match('/^[a-f0-9\-]{36}$/', $value)) {
            return $value;
        }
        return '';
    }
}
