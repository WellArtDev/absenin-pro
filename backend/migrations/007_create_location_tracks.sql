CREATE TABLE location_tracks (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    tenant_id VARCHAR(36) NOT NULL,
    employee_id VARCHAR(36) NOT NULL,
    session_id VARCHAR(36) NOT NULL,
    gps_lat DECIMAL(10, 7) NOT NULL,
    gps_lng DECIMAL(10, 7) NOT NULL,
    accuracy DECIMAL(8, 2),
    recorded_at TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    INDEX idx_track_session (session_id, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
