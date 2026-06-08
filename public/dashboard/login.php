<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/Auth.php';
require_once __DIR__ . '/../../app/helpers/Response.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi';
    } else {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'SELECT id, tenant_id, name, email, password, role FROM users WHERE email = ? AND is_active = true'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            Auth::setSession($user);
            header('Location: /dashboard/home.php');
            exit;
        } else {
            $error = 'Email atau password salah';
        }
    }
}

$isLoggedIn = session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id']);
if ($isLoggedIn) {
    header('Location: /dashboard/home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absenin — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/dashboard/assets/css/variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-family);
            background: var(--color-background);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: var(--color-surface);
            border-radius: var(--rounded-lg);
            box-shadow: var(--shadow-lg);
            padding: var(--space-4xl);
            width: 100%;
            max-width: 400px;
        }
        .login-card h1 {
            font: var(--text-display-md);
            color: var(--color-text-primary);
            margin-bottom: var(--space-sm);
        }
        .login-card p {
            font: var(--text-body);
            color: var(--color-text-secondary);
            margin-bottom: var(--space-3xl);
        }
        .form-group {
            margin-bottom: var(--space-lg);
        }
        .form-group label {
            display: block;
            font: var(--text-body-sm);
            font-weight: 500;
            color: var(--color-text-secondary);
            margin-bottom: var(--space-xs);
        }
        .form-group input {
            width: 100%;
            height: 44px;
            padding: 0 var(--space-md);
            border: 1px solid var(--color-border);
            border-radius: var(--rounded-md);
            font: var(--text-body);
            color: var(--color-text-primary);
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--color-border-focus);
            box-shadow: 0 0 0 3px var(--color-primary-100);
        }
        .btn {
            width: 100%;
            height: 44px;
            background: var(--color-primary-600);
            color: var(--color-text-inverse);
            border: none;
            border-radius: var(--rounded-md);
            font: var(--text-button);
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn:hover { background: var(--color-primary-700); }
        .error {
            background: #FEE2E2;
            color: #991B1B;
            padding: var(--space-md);
            border-radius: var(--rounded-md);
            margin-bottom: var(--space-lg);
            font: var(--text-body-sm);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Absenin</h1>
        <p>Masuk ke dashboard HR</p>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Masuk</button>
        </form>
    </div>
</body>
</html>
