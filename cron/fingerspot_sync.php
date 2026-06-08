<?php
/**
 * Sync attendance data from Fingerspot machine's local MySQL database.
 * Run via cron every 5 minutes: php cron/fingerspot_sync.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Model.php';

$db = Database::getInstance();
$tenants = $db->query(
    "SELECT t.id, t.name FROM tenants t
     JOIN tenant_settings s ON s.tenant_id = t.id AND s.setting_key = 'fingerspot_enabled' AND s.setting_value = '1'
     WHERE t.is_active = true"
)->fetchAll();

$totalSynced = 0;
$totalUnmapped = 0;
$totalErrors = 0;

foreach ($tenants as $tenant) {
    $tid = $tenant['id'];

    try {
        $fsHost = getSetting($db, $tid, 'fingerspot_host');
        $fsDb = getSetting($db, $tid, 'fingerspot_database');
        $fsUser = getSetting($db, $tid, 'fingerspot_user');
        $fsPass = getSetting($db, $tid, 'fingerspot_pass');
        $lastLogId = getSetting($db, $tid, 'fingerspot_last_log_id') ?: '0';

        if (empty($fsHost) || empty($fsDb)) {
            continue;
        }

        $fsConn = new PDO(
            "mysql:host={$fsHost};dbname={$fsDb};charset=utf8",
            $fsUser, $fsPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $fsConn->prepare(
            "SELECT id, pin, datetime, status FROM att_log WHERE id > ? ORDER BY id LIMIT 100"
        );
        $stmt->execute([$lastLogId]);
        $logs = $stmt->fetchAll();

        $employeeModel = new class($tid) extends Model {};
        $attendanceModel = new class($tid) extends Model {};

        $mappings = $employeeModel->query(
            "SELECT e.id, s.setting_value as fingerspot_pin
             FROM employees e
             JOIN tenant_settings s ON s.setting_key = CONCAT('fingerspot_map_', e.id)
             WHERE e.tenant_id = ? AND e.is_active = true",
            [$tid]
        );

        $pinToEmployee = [];
        foreach ($mappings as $m) {
            if ($m['fingerspot_pin']) $pinToEmployee[$m['fingerspot_pin']] = $m['id'];
        }

        foreach ($logs as $log) {
            $pin = (string) $log['pin'];
            if (!isset($pinToEmployee[$pin])) {
                $totalUnmapped++;
                continue;
            }

            $employeeId = $pinToEmployee[$pin];
            $timestamp = $log['datetime'];
            $status = $log['status'] == '1' ? 'clock_out' : 'clock_in';

            if ($status === 'clock_in') {
                $attendanceModel->insert('attendance_sessions', [
                    'tenant_id' => $tid,
                    'employee_id' => $employeeId,
                    'clock_in' => $timestamp,
                    'status' => STATUS_PRESENT,
                    'source' => SOURCE_FINGERPRINT,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $activeSession = $attendanceModel->queryOne(
                    'SELECT id FROM attendance_sessions WHERE employee_id = ? AND tenant_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1',
                    [$employeeId, $tid]
                );
                if ($activeSession) {
                    $attendanceModel->update('attendance_sessions', [
                        'clock_out' => $timestamp,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$activeSession['id']]);
                }
            }
            $totalSynced++;
        }

        $lastId = end($logs)['id'] ?? $lastLogId;
        upsertSetting($db, $tid, 'fingerspot_last_log_id', $lastId);

    } catch (\Exception $e) {
        $totalErrors++;
        error_log("[Fingerspot Sync] Tenant {$tid}: " . $e->getMessage());
    }
}

echo date('Y-m-d H:i:s') . " | Synced: {$totalSynced} | Unmapped: {$totalUnmapped} | Errors: {$totalErrors}\n";

function getSetting(PDO $db, string $tenantId, string $key): string
{
    $stmt = $db->prepare('SELECT setting_value FROM tenant_settings WHERE tenant_id = ? AND setting_key = ?');
    $stmt->execute([$tenantId, $key]);
    return $stmt->fetchColumn() ?: '';
}

function upsertSetting(PDO $db, string $tenantId, string $key, string $value): void
{
    $stmt = $db->prepare(
        'INSERT INTO tenant_settings (id, tenant_id, setting_key, setting_value, created_at, updated_at)
         VALUES (UUID(), ?, ?, ?, NOW(), NOW())
         ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()'
    );
    $stmt->execute([$tenantId, $key, $value, $value]);
}
