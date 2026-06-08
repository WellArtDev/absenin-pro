<?php
/**
 * Load .env file — no dependency, pure PHP.
 */

if (file_exists(ROOT_DIR . '/.env')) {
    $lines = file(ROOT_DIR . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}
