<?php

class AttendanceSession extends Model
{
    public function clockIn(array $data): string
    {
        $data['tenant_id'] = $this->tenantId;
        $data['status'] = STATUS_PRESENT;
        $data['clock_in'] = $this->now();
        $data['created_at'] = $this->now();
        $data['updated_at'] = $this->now();
        return $this->insert('attendance_sessions', $data);
    }

    public function clockOut(string $sessionId, string $employeeId): ?array
    {
        $session = $this->queryOne(
            'SELECT * FROM attendance_sessions WHERE id = ? AND employee_id = ? AND tenant_id = ?',
            [$sessionId, $employeeId, $this->tenantId]
        );

        if (!$session || $session['clock_out'] !== null) {
            return null;
        }

        $clockOut = $this->now();
        $normalHours = DateHelper::diffInHours($session['clock_in'], $clockOut);
        $overtimeHours = 0;

        $settings = $this->getOvertimeSettings();
        $workEnd = $settings['work_end'];

        if ($session['status'] === STATUS_OVERTIME) {
            $overtimeEnd = strtotime($clockOut);
            $overtimeStart = strtotime($workEnd);
            if ($overtimeEnd > $overtimeStart) {
                $overtimeHours = round(($overtimeEnd - $overtimeStart) / 3600, 2);
            }
            $normalHours = $normalHours - $overtimeHours;
        }

        $this->update('attendance_sessions', [
            'clock_out' => $clockOut,
            'normal_hours' => $normalHours,
            'overtime_hours' => $overtimeHours,
            'updated_at' => $this->now(),
        ], 'id = ?', [$sessionId]);

        return $this->queryOne('SELECT * FROM attendance_sessions WHERE id = ?', [$sessionId]);
    }

    public function findActive(string $employeeId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM attendance_sessions WHERE employee_id = ? AND tenant_id = ? AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1',
            [$employeeId, $this->tenantId]
        );
    }

    public function findToday(string $employeeId): ?array
    {
        $today = DateHelper::today();
        return $this->queryOne(
            "SELECT * FROM attendance_sessions WHERE employee_id = ? AND tenant_id = ? AND DATE(clock_in) = ? ORDER BY clock_in DESC LIMIT 1",
            [$employeeId, $this->tenantId, $today]
        );
    }

    public function findByDate(string $date, int $page = 1, int $limit = 20): array
    {
        $sql = "SELECT s.*, e.name as employee_name, e.employee_code
                FROM attendance_sessions s
                JOIN employees e ON e.id = s.employee_id
                WHERE s.tenant_id = ? AND DATE(s.clock_in) = ?
                ORDER BY s.clock_in DESC";
        return $this->paginate($sql, [$this->tenantId, $date], $page, $limit);
    }

    public function getSummary(string $date): array
    {
        return $this->query(
            "SELECT
                COUNT(DISTINCT employee_id) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN status = 'lembur' THEN 1 ELSE 0 END) as lembur
             FROM attendance_sessions
             WHERE tenant_id = ? AND DATE(clock_in) = ?",
            [$this->tenantId, $date]
        )[0] ?? ['total' => 0, 'hadir' => 0, 'terlambat' => 0, 'lembur' => 0];
    }

    public function getReport(string $startDate, string $endDate): array
    {
        return $this->query(
            "SELECT
                e.name, e.employee_code,
                COUNT(DISTINCT DATE(s.clock_in)) as work_days,
                COALESCE(SUM(s.normal_hours), 0) as total_normal,
                COALESCE(SUM(s.overtime_hours), 0) as total_overtime
             FROM employees e
             LEFT JOIN attendance_sessions s ON s.employee_id = e.id
                AND s.tenant_id = ? AND DATE(s.clock_in) BETWEEN ? AND ?
             WHERE e.tenant_id = ? AND e.is_active = true
             GROUP BY e.id, e.name, e.employee_code
             ORDER BY e.name",
            [$this->tenantId, $startDate, $endDate, $this->tenantId]
        );
    }

    public function dispute(string $sessionId, string $employeeId, string $reason): bool
    {
        return $this->update('attendance_sessions', [
            'is_disputed' => true,
            'dispute_reason' => $reason,
            'updated_at' => $this->now(),
        ], 'id = ? AND employee_id = ? AND tenant_id = ?', [$sessionId, $employeeId, $this->tenantId]) > 0;
    }

    public function resolveDispute(string $sessionId, ?float $overtimeHours): bool
    {
        $data = ['dispute_resolved' => true, 'updated_at' => $this->now()];
        if ($overtimeHours !== null) {
            $data['overtime_hours'] = $overtimeHours;
        }
        return $this->update('attendance_sessions', $data, 'id = ? AND tenant_id = ?', [$sessionId, $this->tenantId]) > 0;
    }

    private function getOvertimeSettings(): array
    {
        $row = $this->queryOne(
            "SELECT setting_value FROM tenant_settings WHERE tenant_id = ? AND setting_key = 'work_end'",
            [$this->tenantId]
        );
        return ['work_end' => $row['setting_value'] ?? '17:00:00'];
    }
}
