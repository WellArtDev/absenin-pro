<?php

require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

$logTable = 'migrations_log';
$migrationsDir = __DIR__;

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
        continue;
    }

    $sql = file_get_contents($file);
    $db->exec($sql);

    $stmt = $db->prepare("INSERT INTO {$logTable} (migration) VALUES (?)");
    $stmt->execute([$name]);

    echo "  ✓ {$name}\n";
    $count++;
}

if ($count === 0) {
    echo "  Nothing to migrate.\n";
} else {
    echo "\n  {$count} migration(s) executed.\n";
}
