<?php
function renderHeader(string $title, string $activeMenu): void { ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — Absenin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/dashboard/assets/css/variables.css">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        body{font-family:var(--font-family);background:var(--color-background);display:flex;min-height:100vh}
        .sidebar{width:var(--sidebar-width);background:var(--color-surface);border-right:1px solid var(--color-border);position:fixed;top:0;left:0;bottom:0;display:flex;flex-direction:column;z-index:10}
        .sidebar-brand{padding:var(--space-xl) var(--space-2xl);border-bottom:1px solid var(--color-border)}
        .sidebar-brand h2{font:var(--text-heading);color:var(--color-primary-600)}
        .sidebar-nav{flex:1;padding:var(--space-md);overflow-y:auto}
        .sidebar-nav a{display:flex;align-items:center;gap:var(--space-md);padding:var(--space-md) var(--space-lg);border-radius:var(--rounded-md);font:var(--text-body);color:var(--color-text-secondary);text-decoration:none;transition:background 0.15s;margin-bottom:2px}
        .sidebar-nav a:hover{background:var(--color-neutral-50)}
        .sidebar-nav a.active{background:var(--color-primary-50);color:var(--color-primary-700);font-weight:500}
        .sidebar-section{font:var(--text-caption);color:var(--color-text-tertiary);text-transform:uppercase;letter-spacing:0.5px;padding:var(--space-lg) var(--space-lg) var(--space-sm)}
        .sidebar-user{padding:var(--space-lg) var(--space-2xl);border-top:1px solid var(--color-border);font:var(--text-body-sm);color:var(--color-text-secondary)}
        .main{margin-left:var(--sidebar-width);padding:var(--space-3xl);flex:1;min-width:0}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--space-2xl)}
        .page-header h1{font:var(--text-display-md);color:var(--color-text-primary)}
        .btn{height:44px;padding:0 var(--space-xl);border:none;border-radius:var(--rounded-md);font:var(--text-button);cursor:pointer;display:inline-flex;align-items:center;gap:var(--space-sm);transition:background 0.15s}
        .btn-primary{background:var(--color-primary-600);color:var(--color-text-inverse)}.btn-primary:hover{background:var(--color-primary-700)}
        .btn-secondary{background:var(--color-primary-50);color:var(--color-primary-700);border:1px solid var(--color-primary-200)}.btn-secondary:hover{background:var(--color-primary-100)}
        .btn-danger{background:var(--color-danger);color:var(--color-text-inverse)}.btn-danger:hover{background:#DC2626}
        .btn-ghost{background:transparent;color:var(--color-text-secondary)}.btn-ghost:hover{background:var(--color-neutral-100)}
        .card{background:var(--color-surface);border-radius:var(--rounded-lg);box-shadow:var(--shadow-sm);padding:var(--space-xl)}
        .card+.card{margin-top:var(--space-lg)}
        .search-bar{display:flex;gap:var(--space-md);align-items:center}
        .input{height:44px;padding:0 var(--space-md);border:1px solid var(--color-border);border-radius:var(--rounded-md);font:var(--text-body);color:var(--color-text-primary)}
        .input:focus{outline:none;border-color:var(--color-border-focus);box-shadow:0 0 0 3px var(--color-primary-100)}
        .input-search{border-radius:var(--rounded-full);background:var(--color-neutral-100);border:none;width:280px}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;font:var(--text-caption);color:var(--color-text-tertiary);text-transform:uppercase;padding:var(--space-md) var(--space-lg);border-bottom:1px solid var(--color-border)}
        td{padding:var(--space-md) var(--space-lg);border-bottom:1px solid var(--color-neutral-100);font:var(--text-body);color:var(--color-text-primary)}
        tr:hover td{background:var(--color-neutral-50)}
        .badge{display:inline-flex;align-items:center;gap:5px;padding:2px 10px;border-radius:var(--rounded-full);font:var(--text-caption);font-weight:500}
        .badge-success{background:var(--color-primary-50);color:var(--color-primary-700)}
        .badge-warning{background:#FEF3C7;color:#92400E}
        .badge-danger{background:#FEE2E2;color:#991B1B}
        .badge-info{background:#DBEAFE;color:#1E40AF}
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--space-lg);margin-bottom:var(--space-2xl)}
        .stat-card{background:var(--color-surface);border-radius:var(--rounded-lg);box-shadow:var(--shadow-sm);padding:var(--space-xl)}
        .stat-card .label{font:var(--text-body-sm);color:var(--color-text-tertiary);margin-bottom:var(--space-sm)}
        .stat-card .value{font:var(--text-display-md);color:var(--color-text-primary)}
        .modal-overlay{position:fixed;inset:0;background:var(--color-overlay);display:flex;align-items:center;justify-content:center;z-index:100}
        .modal{background:var(--color-surface);border-radius:var(--rounded-xl);box-shadow:var(--shadow-xl);padding:var(--space-3xl);width:100%;max-width:500px;max-height:80vh;overflow-y:auto}
        .modal h3{font:var(--text-heading);margin-bottom:var(--space-xl)}
        .form-group{margin-bottom:var(--space-lg)}
        .form-group label{display:block;font:var(--text-body-sm);font-weight:500;color:var(--color-text-secondary);margin-bottom:var(--space-xs)}
        .form-group .input{width:100%}
        .form-actions{display:flex;gap:var(--space-md);justify-content:flex-end;margin-top:var(--space-2xl)}
        .toast{position:fixed;top:var(--space-xl);right:var(--space-xl);background:var(--color-surface);border-radius:var(--rounded-lg);box-shadow:var(--shadow-lg);padding:var(--space-lg) var(--space-xl);font:var(--text-body);z-index:200;animation:slideIn 0.2s ease}
        .toast-success{border-left:3px solid var(--color-primary-600)}
        .toast-error{border-left:3px solid var(--color-danger)}
        @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
        .empty{text-align:center;padding:var(--space-6xl) var(--space-3xl);color:var(--color-text-tertiary);font:var(--text-body)}
        .empty-icon{font-size:48px;margin-bottom:var(--space-lg);opacity:0.3}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand"><h2>Absenin</h2></div>
    <nav class="sidebar-nav">
        <a href="/dashboard/home.php" class="<?= $activeMenu === 'home' ? 'active' : '' ?>">🏠 Dashboard</a>
        <a href="/dashboard/views/employees.php" class="<?= $activeMenu === 'employees' ? 'active' : '' ?>">👥 Karyawan</a>
        <a href="/dashboard/views/attendance.php" class="<?= $activeMenu === 'attendance' ? 'active' : '' ?>">📋 Presensi</a>
        <a href="/dashboard/views/overtime.php" class="<?= $activeMenu === 'overtime' ? 'active' : '' ?>">⏰ Lembur</a>
        <a href="/dashboard/views/leave.php" class="<?= $activeMenu === 'leave' ? 'active' : '' ?>">📝 Cuti & Izin</a>
        <a href="/dashboard/views/report.php" class="<?= $activeMenu === 'report' ? 'active' : '' ?>">📊 Report</a>
        <a href="/dashboard/views/clients.php" class="<?= $activeMenu === 'clients' ? 'active' : '' ?>">📍 Klien</a>
        <a href="/dashboard/views/settings.php" class="<?= $activeMenu === 'settings' ? 'active' : '' ?>">⚙️ Pengaturan</a>
    </nav>
    <div class="sidebar-user">👤 <?= htmlspecialchars($GLOBALS['auth_user_name'] ?? 'User') ?></div>
</div>
<div class="main">
<?php } function renderFooter(): void { ?>
</div>
<script src="/dashboard/assets/js/api.js"></script>
</body>
</html>
<?php } ?>
