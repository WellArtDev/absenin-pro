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

    public static function requireRole(object $jwt, array $allowedRoles): void
    {
        $role = $jwt->role ?? 'employee';
        if (!in_array($role, $allowedRoles)) {
            Response::forbidden('Akses ditolak — role tidak diizinkan');
        }
    }

    public static function web(): array
    {
        return Auth::requireSession();
    }
}
