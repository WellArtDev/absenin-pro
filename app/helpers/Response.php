<?php

class Response
{
    public static function json(mixed $data, int $status = 200, ?array $meta = null): never
    {
        http_response_code($status);

        $response = ['success' => $status < 400];

        if ($status < 400) {
            $response['data'] = $data;
            if ($meta !== null) {
                $response['meta'] = $meta;
            }
        } else {
            $response['error'] = $data['error'] ?? 'unknown_error';
            $response['message'] = $data['message'] ?? 'An error occurred';
            if (isset($data['details'])) {
                $response['details'] = $data['details'];
            }
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data, ?array $meta = null): never
    {
        self::json($data, 200, $meta);
    }

    public static function created(mixed $data): never
    {
        self::json($data, 201);
    }

    public static function noContent(): never
    {
        http_response_code(204);
        exit;
    }

    public static function error(string $error, string $message, int $status = 400, ?array $details = null): never
    {
        self::json(['error' => $error, 'message' => $message, 'details' => $details], $status);
    }

    public static function unauthorized(string $message = 'Unauthorized'): never
    {
        self::error('unauthorized', $message, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): never
    {
        self::error('forbidden', $message, 403);
    }

    public static function notFound(string $message = 'Not found'): never
    {
        self::error('not_found', $message, 404);
    }

    public static function validationError(string $message, array $details = []): never
    {
        self::error('validation_failed', $message, 422, $details);
    }

    public static function rateLimited(string $message = 'Too many requests'): never
    {
        self::error('rate_limited', $message, 429);
    }

    public static function serverError(string $message = 'Internal server error'): never
    {
        self::error('server_error', $message, 500);
    }
}
