<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Auth.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    public function login(): void
    {
        $input = Request::getJson();

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            Response::validationError('Email dan password wajib diisi');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT u.id, u.tenant_id, u.name, u.email, u.password, u.role, t.name as tenant_name
             FROM users u
             JOIN tenants t ON t.id = u.tenant_id
             WHERE u.email = ? AND u.is_active = true AND t.is_active = true'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            Response::unauthorized('Email atau password salah');
        }

        $now = time();
        $accessPayload = [
            'iss' => APP_NAME,
            'sub' => $user['id'],
            'tenant_id' => $user['tenant_id'],
            'role' => $user['role'],
            'iat' => $now,
            'exp' => $now + JWT_ACCESS_EXPIRY,
        ];
        $refreshPayload = [
            'iss' => APP_NAME,
            'sub' => $user['id'],
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + JWT_REFRESH_EXPIRY,
        ];

        $accessToken = JWT::encode($accessPayload, JWT_SECRET, JWT_ALGORITHM);
        $refreshToken = JWT::encode($refreshPayload, JWT_SECRET, JWT_ALGORITHM);

        Response::success([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => JWT_ACCESS_EXPIRY,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'tenant_id' => $user['tenant_id'],
                'tenant_name' => $user['tenant_name'],
            ],
        ]);
    }

    public function refresh(): void
    {
        $input = Request::getJson();
        $refreshToken = $input['refresh_token'] ?? '';

        if (empty($refreshToken)) {
            Response::validationError('Refresh token wajib diisi');
        }

        try {
            $decoded = JWT::decode($refreshToken, new Key(JWT_SECRET, JWT_ALGORITHM));

            if (($decoded->type ?? '') !== 'refresh') {
                Response::unauthorized('Invalid refresh token');
            }

            $now = time();
            $accessPayload = [
                'iss' => APP_NAME,
                'sub' => $decoded->sub,
                'tenant_id' => $decoded->tenant_id ?? null,
                'role' => $decoded->role ?? null,
                'iat' => $now,
                'exp' => $now + JWT_ACCESS_EXPIRY,
            ];

            $accessToken = JWT::encode($accessPayload, JWT_SECRET, JWT_ALGORITHM);

            Response::success([
                'access_token' => $accessToken,
                'expires_in' => JWT_ACCESS_EXPIRY,
            ]);
        } catch (\Exception $e) {
            Response::unauthorized('Invalid or expired refresh token');
        }
    }
}
