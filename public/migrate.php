<?php
/**
 * Web-accessible migration runner for cPanel (no SSH).
 * Access: https://api.absenin.com/migrate.php?key=YOUR_SECRET_KEY
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$requiredKey = getenv('MIGRATE_KEY') ?: 'migrate-absenin-2026';
$providedKey = $_GET['key'] ?? '';

if (empty($providedKey) || $providedKey !== $requiredKey) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'Invalid or missing key']));
}

header('Content-Type: application/json; charset=utf-8');

$output = [];
$db = Database::getInstance();

$logTable = 'migrations_log';
$migrationsDir = __DIR__ . '/../migrations';

try {
    $db->exec("CREATE TABLE IF NOT EXISTS {$logTable} (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $executed = $db->query("SELECT migration FROM {$logTable}")->fetchAll(PDO::FETCH_COLUMN);

    $files = glob($migrationsDir . '/*.sql');
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
