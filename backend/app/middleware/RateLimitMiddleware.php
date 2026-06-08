<?php

class RateLimitMiddleware
{
    public static function check(string $key): void
    {
        $db = Database::getInstance();

        $window = RATE_LIMIT_WINDOW;
        $max = RATE_LIMIT_MAX;
        $now = date('Y-m-d H:i:s');

        $stmt = $db->prepare(
            "INSERT INTO rate_limits (rate_key, hits, window_start)
             VALUES (?, 1, ?)
             ON DUPLICATE KEY UPDATE
                hits = IF(window_start > DATE_SUB(NOW(), INTERVAL ? SECOND), hits + 1, 1),
                window_start = IF(window_start > DATE_SUB(NOW(), INTERVAL ? SECOND), window_start, ?)"
        );
        $stmt->execute([$key, $now, $window, $window, $now]);

        $stmt = $db->prepare(
            'SELECT hits FROM rate_limits WHERE rate_key = ? AND window_start > DATE_SUB(NOW(), INTERVAL ? SECOND)'
        );
        $stmt->execute([$key, $window]);
        $row = $stmt->fetch();

        if ($row && (int) $row['hits'] > $max) {
            Response::rateLimited();
        }
    }
}
