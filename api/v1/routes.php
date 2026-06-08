<?php

$method = $_SERVER['REQUEST_METHOD'];
$path = $path ?? '/';
$path = rtrim($path, '/') ?: '/';

// Health check
if ($path === '/' && $method === 'GET') {
    Response::success([
        'name' => APP_NAME,
        'version' => '1.0.0',
        'status' => 'ok',
    ]);
    exit;
}

// Auth routes
if ($path === '/auth/login' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    (new AuthController())->login();
    exit;
}

if ($path === '/auth/refresh' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    (new AuthController())->refresh();
    exit;
}

// 404 fallback
Response::notFound('Endpoint not found');
