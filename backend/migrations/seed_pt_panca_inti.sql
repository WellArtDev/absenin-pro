-- Seed: PT Panca Inti Bermitra
-- Jalankan via phpMyAdmin atau cPanel MySQL

-- 1. Tenant
INSERT INTO tenants (id, name, subdomain, is_active, created_at, updated_at)
VALUES (UUID(), 'PT Panca Inti Bermitra', 'pancainti', 1, NOW(3), NOW(3));

-- 2. Ambil tenant_id yang baru dibuat
SET @tenant_id = (SELECT id FROM tenants WHERE subdomain = 'pancainti' LIMIT 1);

-- 3. Admin user (password: admin123)
INSERT INTO users (id, tenant_id, name, email, password, role, is_active, created_at, updated_at)
VALUES (
  UUID(),
  @tenant_id,
  'Admin Panca Inti',
  'admin@pancainti.com',
  '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK',
  'admin',
  1,
  NOW(3),
  NOW(3)
);

-- 5. Employee users (for mobile login, password: admin123)
INSERT INTO users (id, tenant_id, name, email, password, role, is_active, created_at, updated_at)
VALUES
  (UUID(), @tenant_id, 'Budi Santoso', 'budi@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Siti Rahayu', 'siti@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Ahmad Fauzi', 'ahmad@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Dewi Lestari', 'dewi@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Rudi Hermawan', 'rudi@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3));

-- 3. Tenant settings
INSERT INTO tenant_settings (tenant_id, setting_key, setting_value)
VALUES
  (@tenant_id, 'work_start', '09:00'),
  (@tenant_id, 'work_end', '17:00'),
  (@tenant_id, 'tolerance_minutes', '15'),
  (@tenant_id, 'overtime_mode', 'auto'),
  (@tenant_id, 'gps_default_mode', 'spesifik');

-- 6. Employee records (detail HR)
INSERT INTO employees (id, tenant_id, employee_code, name, email, phone, ktp_number, npwp_number, birth_date, birth_place, address, emergency_contact_name, emergency_contact_phone, division_id, position_id, employment_status, join_date, leave_balance, is_active, created_at, updated_at) VALUES
(UUID(), @tenant_id, 'PIM001', 'Budi Santoso',   'budi@pancainti.com',   '08123456789', '3174010101920001', '-', '1992-03-15', 'Jakarta',        'Jl. Kenanga No. 12, Jakarta Selatan',  'Sari Wahyuni', '08129876543', 'DIV-01', 'POS-01', 'tetap',    '2024-01-15', 10, 1, NOW(3), NOW(3)),
(UUID(), @tenant_id, 'PIM002', 'Siti Rahayu',    'siti@pancainti.com',   '08123456780', '3273014507950002', '-', '1995-07-22', 'Bandung',        'Jl. Melati No. 34, Bandung',           'Ahmad Dahlan',  '08129876544', 'DIV-02', 'POS-02', 'tetap',    '2024-02-01', 12, 1, NOW(3), NOW(3)),
(UUID(), @tenant_id, 'PIM003', 'Ahmad Fauzi',    'ahmad@pancainti.com',  '08123456781', '3578010811900001', '-', '1990-11-08', 'Surabaya',       'Jl. Mawar No. 56, Surabaya',           'Fatimah Zahra', '08129876545', 'DIV-01', 'POS-03', 'tetap',    '2024-01-10',  8, 1, NOW(3), NOW(3)),
(UUID(), @tenant_id, 'PIM004', 'Dewi Lestari',   'dewi@pancainti.com',   '08123456782', '3174014205930003', '-', '1993-05-30', 'Jakarta',        'Jl. Anggrek No. 78, Jakarta Pusat',    'Budi Hartono',  '08129876546', 'DIV-03', 'POS-04', 'kontrak',  '2024-06-01', 12, 1, NOW(3), NOW(3)),
(UUID(), @tenant_id, 'PIM005', 'Rudi Hermawan',  'rudi@pancainti.com',   '08123456783', '1271010909880002', '-', '1988-09-12', 'Medan',          'Jl. Dahlia No. 90, Medan',             'Rina Marlina',  '08129876547', 'DIV-02', 'POS-05', 'tetap',    '2023-12-01',  5, 1, NOW(3), NOW(3));

-- 7. Client/lokasi
INSERT INTO clients (id, tenant_id, name, address, gps_lat, gps_lng, radius_meters, created_at, updated_at)
VALUES
  (UUID(), @tenant_id, 'Kantor Pusat', 'Jl. Sudirman No. 123, Jakarta', -6.2088, 106.8456, 10, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Gudang Cakung', 'Jl. Raya Cakung No. 45, Jakarta Timur', -6.1823, 106.9492, 15, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Cabang Bandung', 'Jl. Asia Afrika No. 88, Bandung', -6.9175, 107.6191, 10, NOW(3), NOW(3));

-- Verify
SELECT 'Tenant:', id, name FROM tenants WHERE subdomain = 'pancainti';
SELECT 'Users:', COUNT(*) FROM users WHERE tenant_id = @tenant_id;
SELECT 'Employees:', COUNT(*) FROM employees WHERE tenant_id = @tenant_id;
SELECT 'Clients:', COUNT(*) FROM clients WHERE tenant_id = @tenant_id;
