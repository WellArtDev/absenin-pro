<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class Auth
{
    public static function requireJwt(): object
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(.+)$/', $header, $matches)) {
            Response::unauthorized('Token tidak ditemukan');
        }

        return self::decodeJwt($matches[1]);
    }

    public static function requireJwtFromCookie(string $token): object
    {
        return self::decodeJwt($token);
    }

    private static function decodeJwt(string $token): object
    {
        try {
            return JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
        } catch (ExpiredException $e) {
            Response::unauthorized('Token kadaluarsa');
        } catch (\Exception $e) {
            Response::unauthorized('Token tidak valid');
        }
    }

    public static function requireSession(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id']) || empty($_SESSION['tenant_id'])) {
            header('Location: /dashboard/login.php');
            exit;
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'tenant_id' => $_SESSION['tenant_id'],
            'role' => $_SESSION['role'] ?? 'employee',
        ];
    }

    public static function generateJwt(array $user): array
    {
        $issuedAt = time();
        $accessExpiry = $issuedAt + JWT_ACCESS_EXPIRY;
        $refreshExpiry = $issuedAt + JWT_REFRESH_EXPIRY;

        $accessPayload = [
            'iss' => APP_URL,
            'iat' => $issuedAt,
            'exp' => $accessExpiry,
            'sub' => $user['id'],
            'tenant_id' => $user['tenant_id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'name' => $user['name'],
            'type' => 'access',
        ];

        $refreshPayload = [
            'iss' => APP_URL,
            'iat' => $issuedAt,
            'exp' => $refreshExpiry,
            'sub' => $user['id'],
            'tenant_id' => $user['tenant_id'],
            'type' => 'refresh',
        ];

        return [
            'access_token' => JWT::encode($accessPayload, JWT_SECRET, JWT_ALGORITHM),
            'refresh_token' => JWT::encode($refreshPayload, JWT_SECRET, JWT_ALGORITHM),
            'expires_in' => JWT_ACCESS_EXPIRY,
        ];
    }

    public static function setSession(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
    }

    public static function destroySession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
    }
}
