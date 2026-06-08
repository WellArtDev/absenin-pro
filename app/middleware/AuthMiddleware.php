<?php

class AuthMiddleware
{
    public static function api(): object
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/^Bearer\s+(.+)$/', $header, $matches)) {
            return Auth::requireJwt();
        }

        if (!empty($_COOKIE['jwt'])) {
            return Auth::requireJwtFromCookie($_COOKIE['jwt']);
        }

        Response::unauthorized('Token tidak ditemukan');
    }

    public static function web(): array
    {
        return Auth::requireSession();
    }
}
