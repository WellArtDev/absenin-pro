<?php

class LeaveRequest extends Model
{
    public function findAll(int $page = 1, int $limit = 20, string $status = ''): array
    {
        $params = [$this->tenantId];
        $where = 'WHERE lr.tenant_id = ?';
        if ($status) {
            $where .= ' AND lr.status = ?';
            $params[] = $status;
        }
        $sql = "SELECT lr.*, e.name as employee_name, e.employee_code
                FROM leave_requests lr
                JOIN employees e ON e.id = lr.employee_id
                {$where} ORDER BY lr.created_at DESC";
        return $this->paginate($sql, $params, $page, $limit);
    }

    public function create(array $data): string
    {
        $start = new DateTime($data['start_date']);
        $end = new DateTime($data['end_date']);
        $days = (int) $start->diff($end)->days + 1;
        $data['days_count'] = $days;
        $data['tenant_id'] = $this->tenantId;
        $data['status'] = APPROVAL_PENDING;
        $data['created_at'] = $this->now();
        $data['updated_at'] = $this->now();
        return $this->insert('leave_requests', $data);
    }

    public function approve(string $id, string $approvedBy): bool
    {
        $request = $this->queryOne('SELECT * FROM leave_requests WHERE id = ? AND tenant_id = ?', [$id, $this->tenantId]);
        if (!$request || $request['status'] !== APPROVAL_PENDING) return false;

        $this->update('leave_requests', [
            'status' => APPROVAL_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => $this->now(),
            'updated_at' => $this->now(),
        ], 'id = ? AND tenant_id = ?', [$id, $this->tenantId]);

        if ($request['leave_type'] === LEAVE_ANNUAL) {
            $this->update('employees', [
                'leave_balance' => $request['days_count'],
            ], 'id = ? AND leave_balance >= ?', [$request['employee_id'], $request['days_count']]);
        }
        return true;
    }

    public function reject(string $id, string $reason): bool
    {
        return $this->update('leave_requests', [
            'status' => APPROVAL_REJECTED,
            'rejection_reason' => $reason,
            'updated_at' => $this->now(),
        ], 'id = ? AND tenant_id = ? AND status = ?', [$id, $this->tenantId, APPROVAL_PENDING]) > 0;
    }

    public function findConflicts(string $employeeId, string $startDate, string $endDate): array
    {
        return $this->query(
            "SELECT lr.*, e.name as employee_name
             FROM leave_requests lr
             JOIN employees e ON e.id = lr.employee_id
             WHERE lr.tenant_id = ? AND lr.employee_id != ? AND lr.status = ?
               AND lr.start_date <= ? AND lr.end_date >= ?
             ORDER BY lr.start_date",
            [$this->tenantId, $employeeId, APPROVAL_APPROVED, $endDate, $startDate]
        );
    }

    public function getLeaveSummary(string $startDate, string $endDate): array
    {
        return $this->query(
            "SELECT e.name, e.employee_code,
                    COALESCE(SUM(CASE WHEN lr.leave_type = 'cuti_tahunan' THEN lr.days_count ELSE 0 END), 0) as cuti,
                    COALESCE(SUM(CASE WHEN lr.leave_type = 'izin' THEN lr.days_count ELSE 0 END), 0) as izin,
                    COALESCE(SUM(CASE WHEN lr.leave_type = 'sakit' THEN lr.days_count ELSE 0 END), 0) as sakit
             FROM employees e
             LEFT JOIN leave_requests lr ON lr.employee_id = e.id
                AND lr.tenant_id = ? AND lr.status = ? AND lr.start_date >= ? AND lr.end_date <= ?
             WHERE e.tenant_id = ? AND e.is_active = true
             GROUP BY e.id, e.name, e.employee_code",
            [$this->tenantId, APPROVAL_APPROVED, $startDate, $endDate, $this->tenantId]
        );
    }
}
