<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');

$allowedOrigin = getenv('CORS_ORIGINS') ?: '*';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . $allowedOrigin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/helpers/Sanitizer.php';

$_SERVER['REQUEST_URI'] = Sanitizer::string($_SERVER['REQUEST_URI'] ?? '/', 2048);

$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = parse_url($requestUri, PHP_URL_PATH);

$path = preg_replace('#^/api#', '', $requestUri);

require_once __DIR__ . '/../api/v1/routes.php';
