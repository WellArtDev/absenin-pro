<?php

$method = $_SERVER['REQUEST_METHOD'];
$path = $path ?? '/';
$path = rtrim($path, '/') ?: '/';

if ($path === '/' && $method === 'GET') {
    Response::success(['name' => APP_NAME, 'version' => '1.0.0', 'status' => 'ok']);
    exit;
}

if ($path === '/auth/login' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    (new AuthController())->login(); exit;
}

if ($path === '/auth/refresh' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    (new AuthController())->refresh(); exit;
}

require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../app/middleware/TenantMiddleware.php';
require_once __DIR__ . '/../app/middleware/RateLimitMiddleware.php';

$jwt = AuthMiddleware::api();
$tenantId = TenantMiddleware::fromJwt($jwt);
RateLimitMiddleware::check("{$tenantId}:{$method}:{$path}");

if (preg_match('#^/employees$#', $path)) {
    require_once __DIR__ . '/../app/controllers/EmployeeController.php';
    $ctrl = new EmployeeController($tenantId);
    match ($method) {'GET' => $ctrl->index(), 'POST' => $ctrl->store(), default => Response::error('method_not_allowed', 'Method not allowed', 405)};
    exit;
}

if ($path === '/employees/import' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/EmployeeController.php';
    (new EmployeeController($tenantId))->import(); exit;
}

if (preg_match('#^/employees/([a-f0-9\-]{36})$#', $path, $m)) {
    require_once __DIR__ . '/../app/controllers/EmployeeController.php';
    $ctrl = new EmployeeController($tenantId);
    match ($method) {'GET' => $ctrl->show($m[1]), 'PUT' => $ctrl->update($m[1]), 'DELETE' => $ctrl->destroy($m[1]), default => Response::error('method_not_allowed', 'Method not allowed', 405)};
    exit;
}

if ($path === '/attendance/clock-in' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->clockIn(); exit;
}

if ($path === '/attendance/clock-out' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->clockOut(); exit;
}

if (preg_match('#^/attendance/status/([a-f0-9\-]{36})$#', $path, $m) && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->status($m[1]); exit;
}

if ($path === '/attendance/track' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->trackLocation(); exit;
}

if ($path === '/attendance/log' && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->log(); exit;
}

if ($path === '/attendance/summary' && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->summary(); exit;
}

if ($path === '/attendance/report' && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->report(); exit;
}

if ($path === '/attendance/report/csv' && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->reportCsv(); exit;
}

if ($path === '/attendance/dispute' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->dispute(); exit;
}

if ($path === '/attendance/dispute/resolve' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->resolveDispute(); exit;
}

if ($path === '/attendance/locations' && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->activeLocations(); exit;
}

if (preg_match('#^/attendance/tracks/([a-f0-9\-]{36})$#', $path, $m) && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/AttendanceController.php';
    (new AttendanceController($tenantId))->trackHistory($m[1]); exit;
}

if (preg_match('#^/leaves$#', $path)) {
    require_once __DIR__ . '/../app/controllers/LeaveController.php';
    $ctrl = new LeaveController($tenantId);
    match ($method) {'GET' => $ctrl->index(), 'POST' => $ctrl->store(), default => Response::error('method_not_allowed', 'Method not allowed', 405)};
    exit;
}

if (preg_match('#^/leaves/([a-f0-9\-]{36})/approve$#', $path, $m) && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/LeaveController.php';
    (new LeaveController($tenantId))->approve($m[1]); exit;
}

if (preg_match('#^/leaves/([a-f0-9\-]{36})/reject$#', $path, $m) && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/LeaveController.php';
    (new LeaveController($tenantId))->reject($m[1]); exit;
}

if ($path === '/leaves/conflicts' && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/LeaveController.php';
    (new LeaveController($tenantId))->conflicts(); exit;
}

if (preg_match('#^/clients$#', $path)) {
    require_once __DIR__ . '/../app/controllers/ClientController.php';
    $ctrl = new ClientController($tenantId);
    match ($method) {'GET' => $ctrl->index(), 'POST' => $ctrl->store(), default => Response::error('method_not_allowed', 'Method not allowed', 405)};
    exit;
}

if (preg_match('#^/clients/([a-f0-9\-]{36})$#', $path, $m)) {
    require_once __DIR__ . '/../app/controllers/ClientController.php';
    $ctrl = new ClientController($tenantId);
    match ($method) {'PUT' => $ctrl->update($m[1]), 'DELETE' => $ctrl->destroy($m[1]), default => Response::error('method_not_allowed', 'Method not allowed', 405)};
    exit;
}

if ($path === '/devices/register' && $method === 'POST') {
    require_once __DIR__ . '/../app/controllers/DeviceController.php';
    (new DeviceController($tenantId))->register(); exit;
}

if ($path === '/settings' && $method === 'GET') {
    require_once __DIR__ . '/../app/controllers/SettingsController.php';
    (new SettingsController($tenantId))->get(); exit;
}

if ($path === '/settings' && $method === 'PUT') {
    require_once __DIR__ . '/../app/controllers/SettingsController.php';
    (new SettingsController($tenantId))->update(); exit;
}

Response::notFound('Endpoint not found');

