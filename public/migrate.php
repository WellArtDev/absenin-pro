<?php

$requiredKey = getenv('MIGRATE_KEY') ?: 'migrate-absenin-2026';
$providedKey = $_GET['key'] ?? '';

if (empty($providedKey) || $providedKey !== $requiredKey) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Invalid or missing key']));
}

header('Content-Type: application/json; charset=utf-8');

$root = dirname(__DIR__);

if (!file_exists($root . '/.env')) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => '.env file not found', 'hint' => 'Copy .env.example to .env and configure']));
}

if (!file_exists($root . '/vendor/autoload.php')) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'vendor not found', 'hint' => 'Run composer install on server or upload vendor/ folder']));
}

require_once $root . '/config/config.php';
require_once $root . '/config/database.php';

try {
    $db = Database::getInstance();
} catch (\Exception $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'database_connection_failed', 'message' => $e->getMessage()]));
}

$output = [];
$logTable = 'migrations_log';
$migrationsDir = $root . '/migrations';

try {
    $db->exec("CREATE TABLE IF NOT EXISTS {$logTable} (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $executed = $db->query("SELECT migration FROM {$logTable}")->fetchAll(PDO::FETCH_COLUMN);

    $files = glob($migrationsDir . '/*.sql');
    if (empty($files)) {
        die(json_encode(['success' => false, 'error' => 'No SQL migration files found']));
    }
    sort($files);

    $count = 0;
    foreach ($files as $file) {
        $name = basename($file);

        if (in_array($name, $executed)) {
            $output[] = "SKIP: {$name}";
            continue;
        }

        $sql = file_get_contents($file);
        $db->exec($sql);

        $stmt = $db->prepare("INSERT INTO {$logTable} (migration) VALUES (?)");
        $stmt->execute([$name]);

        $output[] = "OK: {$name}";
        $count++;
    }

    if ($count === 0) {
        $output[] = 'Nothing to migrate. All up to date.';
    } else {
        $output[] = "{$count} migration(s) executed.";
    }

    echo json_encode([
        'success' => true,
        'data' => ['executed' => $count, 'log' => $output],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'migration_failed',
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
