<?php

class Request
{
    public static function getJson(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function getQuery(string $key, string $type = 'string', mixed $default = null): mixed
    {
        $value = $_GET[$key] ?? $default;
        return match ($type) {
            'int' => Sanitizer::int($value),
            'float' => Sanitizer::float($value),
            'string' => Sanitizer::string((string) $value),
            'date' => Sanitizer::date($value),
            'email' => Sanitizer::email($value),
            'uuid' => Sanitizer::uuid($value),
            default => Sanitizer::string((string) $value),
        };
    }

    public static function getPagination(): array
    {
        return [
            'page' => max(1, (int) (self::getQuery('page', 'int', 1))),
            'limit' => min((int) (self::getQuery('limit', 'int', PAGE_LIMIT_DEFAULT)), PAGE_LIMIT_MAX),
        ];
    }
}
