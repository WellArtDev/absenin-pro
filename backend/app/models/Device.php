<?php

class Device extends Model
{
    public function register(string $employeeId, string $deviceId, string $platform): string
    {
        $existing = $this->queryOne(
            'SELECT id FROM devices WHERE employee_id = ? AND tenant_id = ? AND is_active = true',
            [$employeeId, $this->tenantId]
        );

        if ($existing) {
            $this->update('devices', ['is_active' => false], 'employee_id = ? AND tenant_id = ?', [$employeeId, $this->tenantId]);
        }

        return $this->insert('devices', [
            'tenant_id' => $this->tenantId,
            'employee_id' => $employeeId,
            'device_id' => $deviceId,
            'platform' => $platform,
            'is_active' => true,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);
    }

    public function validate(string $employeeId, string $deviceId): bool
    {
        $device = $this->queryOne(
            'SELECT id FROM devices WHERE employee_id = ? AND device_id = ? AND tenant_id = ? AND is_active = true',
            [$employeeId, $deviceId, $this->tenantId]
        );
        return $device !== null;
    }
}
