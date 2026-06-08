<?php

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/Upload.php';
require_once __DIR__ . '/../models/AttendanceSession.php';
require_once __DIR__ . '/../models/LocationTrack.php';
require_once __DIR__ . '/../models/Device.php';

class AttendanceController
{
    private string $tenantId;
    private AttendanceSession $session;
    private LocationTrack $track;
    private Device $device;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->session = new AttendanceSession($tenantId);
        $this->track = new LocationTrack($tenantId);
        $this->device = new Device($tenantId);
    }

    public function clockIn(): void
    {
        $employeeId = $_POST['employee_id'] ?? '';
        $deviceId = $_POST['device_id'] ?? '';
        $gpsLat = (float) ($_POST['gps_lat'] ?? 0);
        $gpsLng = (float) ($_POST['gps_lng'] ?? 0);
        $gpsMode = $_POST['gps_mode'] ?? '';
        $clientId = $_POST['client_id'] ?? null;

        if (empty($employeeId) || empty($deviceId)) {
            Response::validationError('Employee ID dan Device ID wajib');
        }

        if (!$this->device->validate($employeeId, $deviceId)) {
            Response::forbidden('Perangkat tidak terdaftar. Hubungi HR.');
        }

        $existing = $this->session->findActive($employeeId);
        if ($existing) {
            Response::validationError('Anda masih memiliki sesi aktif. Silakan clock-out terlebih dahulu.');
        }

        $selfiePath = null;
        if (isset($_FILES['selfie'])) {
            $selfiePath = Upload::selfie($_FILES['selfie'], $this->tenantId, $employeeId);
        }

        $sessionId = $this->session->clockIn([
            'employee_id' => $employeeId,
            'gps_lat' => $gpsLat,
            'gps_lng' => $gpsLng,
            'gps_mode' => $gpsMode,
            'client_id' => $clientId,
            'selfie_path' => $selfiePath,
        ]);

        Response::created([
            'session_id' => $sessionId,
            'status' => 'hadir',
            'timestamp' => date('c'),
        ]);
    }

    public function clockOut(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $employeeId = $input['employee_id'] ?? '';
        $sessionId = $input['session_id'] ?? '';

        if (empty($employeeId) || empty($sessionId)) {
            Response::validationError('Employee ID dan Session ID wajib');
        }

        $result = $this->session->clockOut($sessionId, $employeeId);
        if (!$result) {
            Response::notFound('Sesi tidak ditemukan atau sudah berakhir');
        }

        Response::success($result);
    }

    public function status(string $employeeId): void
    {
        $active = $this->session->findActive($employeeId);
        $today = $this->session->findToday($employeeId);

        Response::success([
            'active_session' => $active,
            'today_attendance' => $today,
        ]);
    }

    public function trackLocation(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = $input['session_id'] ?? '';
        $employeeId = $input['employee_id'] ?? '';
        $lat = (float) ($input['gps_lat'] ?? 0);
        $lng = (float) ($input['gps_lng'] ?? 0);
        $accuracy = isset($input['accuracy']) ? (float) $input['accuracy'] : null;

        if (empty($sessionId)) {
            Response::validationError('Session ID wajib');
        }

        $this->track->addPoint($sessionId, $employeeId, $lat, $lng, $accuracy);
        Response::success(['recorded' => true]);
    }

    public function log(): void
    {
        $date = $_GET['date'] ?? DateHelper::today();
        $page = (int) ($_GET['page'] ?? 1);
        $limit = min((int) ($_GET['limit'] ?? PAGE_LIMIT_DEFAULT), PAGE_LIMIT_MAX);

        $result = $this->session->findByDate($date, $page, $limit);
        Response::success($result['data'], $result['meta']);
    }

    public function summary(): void
    {
        $date = $_GET['date'] ?? DateHelper::today();
        $summary = $this->session->getSummary($date);
        Response::success($summary);
    }

    public function report(): void
    {
        $start = $_GET['start'] ?? DateHelper::today('Y-m-01');
        $end = $_GET['end'] ?? DateHelper::today();

        $data = $this->session->getReport($start, $end);
        Response::success($data);
    }

    public function reportCsv(): void
    {
        $start = $_GET['start'] ?? DateHelper::today('Y-m-01');
        $end = $_GET['end'] ?? DateHelper::today();

        $data = $this->session->getReport($start, $end);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="absenin-report-' . $start . '-' . $end . '.csv"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Nama', 'NIK', 'Hari Kerja', 'Jam Normal', 'Jam Lembur', 'Cuti', 'Izin', 'Sakit']);

        $leaveModel = new LeaveRequest($this->tenantId);
        $leaveSummary = $leaveModel->getLeaveSummary($start, $end);
        $leaveIndex = [];
        foreach ($leaveSummary as $l) {
            $leaveIndex[$l['employee_code']] = $l;
        }

        foreach ($data as $row) {
            $code = $row['employee_code'];
            $l = $leaveIndex[$code] ?? ['cuti' => 0, 'izin' => 0, 'sakit' => 0];
            fputcsv($out, [
                $row['name'], $code, $row['work_days'],
                $row['total_normal'], $row['total_overtime'],
                $l['cuti'], $l['izin'], $l['sakit'],
            ]);
        }
        fclose($out);
        exit;
    }

    public function dispute(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = $input['session_id'] ?? '';
        $employeeId = $input['employee_id'] ?? '';
        $reason = $input['reason'] ?? '';

        if (empty($sessionId) || empty($reason)) {
            Response::validationError('Session ID dan alasan wajib');
        }

        $this->session->dispute($sessionId, $employeeId, $reason);
        Response::success(['disputed' => true]);
    }

    public function resolveDispute(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $sessionId = $input['session_id'] ?? '';
        $overtimeHours = $input['overtime_hours'] ?? null;

        if (empty($sessionId)) {
            Response::validationError('Session ID wajib');
        }

        $this->session->resolveDispute($sessionId, $overtimeHours);
        Response::success(['resolved' => true]);
    }

    public function activeLocations(): void
    {
        $locations = $this->track->getActiveLocations();
        Response::success($locations);
    }

    public function trackHistory(string $sessionId): void
    {
        $tracks = $this->track->getTrack($sessionId);
        Response::success($tracks);
    }
}
