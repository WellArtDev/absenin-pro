<?php
/**
 * Send daily attendance reminders via FCM.
 * Run daily: php cron/send_reminders.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (empty(FCM_SERVER_KEY)) {
    echo "FCM_SERVER_KEY not configured. Skipping.\n";
    exit;
}

$db = Database::getInstance();

$tenants = $db->query(
    "SELECT t.id, t.name FROM tenants t WHERE t.is_active = true"
)->fetchAll();

$sent = 0;

foreach ($tenants as $tenant) {
    $tid = $tenant['id'];

    $reminderMinutes = $db->query(
        "SELECT setting_value FROM tenant_settings WHERE tenant_id = ? AND setting_key = 'reminder_before_minutes'",
        [$tid]
    )->fetchColumn() ?: '30';

    $workStart = $db->query(
        "SELECT setting_value FROM tenant_settings WHERE tenant_id = ? AND setting_key = 'work_start'",
        [$tid]
    )->fetchColumn() ?: '09:00:00';

    $reminderTime = date('H:i:s', strtotime($workStart) - ($reminderMinutes * 60));

    $tokens = $db->prepare(
        "SELECT n.fcm_token, e.name FROM notifications n
         JOIN employees e ON e.id = n.employee_id
         WHERE n.tenant_id = ? AND n.is_active = true AND e.is_active = true
         AND NOT EXISTS (
             SELECT 1 FROM attendance_sessions s
             WHERE s.employee_id = e.id AND DATE(s.clock_in) = CURDATE()
         )"
    );
    $tokens->execute([$tid]);
    $recipients = $tokens->fetchAll();

    foreach ($recipients as $recipient) {
        $result = sendFcm(
            $recipient['fcm_token'],
            'Jangan lupa presensi!',
            "Halo {$recipient['name']}, jam masuk hari ini pukul " . substr($workStart, 0, 5) . " WIB. Segera lakukan presensi."
        );
        if ($result) $sent++;
    }
}

echo date('Y-m-d H:i:s') . " | Reminders sent: {$sent}\n";

function sendFcm(string $token, string $title, string $body): bool
{
    $payload = json_encode([
        'message' => [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ],
    ]);

    $ch = curl_init('https://fcm.googleapis.com/v1/projects/absenin/messages:send');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . FCM_SERVER_KEY,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}
