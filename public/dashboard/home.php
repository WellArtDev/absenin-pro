<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../app/middleware/TenantMiddleware.php';

$auth = AuthMiddleware::web();
$tenantId = $auth['tenant_id'];
$userName = $auth['user_name'];
$role = $auth['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Absenin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/dashboard/assets/css/variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family); display: flex; min-height: 100vh; }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--color-surface);
            border-right: 1px solid var(--color-border);
            padding: var(--space-2xl) var(--space-lg);
            position: fixed; top: 0; left: 0; bottom: 0;
        }
        .main { margin-left: var(--sidebar-width); padding: var(--space-3xl); flex: 1; }
        h2 { font: var(--text-heading); color: var(--color-text-primary); margin-bottom: var(--space-lg); }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3 style="font:var(--text-subheading);color:var(--color-primary-600);margin-bottom:var(--space-3xl)">Absenin</h3>
        <p style="font:var(--text-body-sm);color:var(--color-text-tertiary)">Selamat datang, <?= htmlspecialchars($userName) ?></p>
    </div>
    <div class="main">
        <h2>Dashboard</h2>
        <p style="font:var(--text-body);color:var(--color-text-secondary)">Dashboard HR Absenin — data kehadiran real-time akan tampil di sini.</p>
    </div>
</body>
</html>
