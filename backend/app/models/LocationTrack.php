<?php

class LocationTrack extends Model
{
    public function addPoint(string $sessionId, string $employeeId, float $lat, float $lng, ?float $accuracy): void
    {
        $this->insert('location_tracks', [
            'tenant_id' => $this->tenantId,
            'employee_id' => $employeeId,
            'session_id' => $sessionId,
            'gps_lat' => $lat,
            'gps_lng' => $lng,
            'accuracy' => $accuracy,
            'recorded_at' => $this->now(),
        ]);
    }

    public function getTrack(string $sessionId): array
    {
        return $this->query(
            'SELECT gps_lat, gps_lng, accuracy, recorded_at FROM location_tracks WHERE session_id = ? ORDER BY recorded_at',
            [$sessionId]
        );
    }

    public function getActiveLocations(): array
    {
        return $this->query(
            "SELECT lt.gps_lat, lt.gps_lng, e.name as employee_name, s.clock_in, s.id as session_id
             FROM location_tracks lt
             JOIN attendance_sessions s ON s.id = lt.session_id AND s.clock_out IS NULL
             JOIN employees e ON e.id = lt.employee_id
             WHERE lt.tenant_id = ?
             AND lt.recorded_at = (
                 SELECT MAX(recorded_at) FROM location_tracks WHERE session_id = lt.session_id
             )",
            [$this->tenantId]
        );
    }
}
