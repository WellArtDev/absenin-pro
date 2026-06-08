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

-- 4. Employee data (5 karyawan)
INSERT INTO users (id, tenant_id, name, email, password, role, is_active, created_at, updated_at)
VALUES
  (UUID(), @tenant_id, 'Budi Santoso', 'budi@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Siti Rahayu', 'siti@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Ahmad Fauzi', 'ahmad@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Dewi Lestari', 'dewi@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3)),
  (UUID(), @tenant_id, 'Rudi Hermawan', 'rudi@pancainti.com', '$2y$10$SZGyy5wCGIuz4kHWaWYoLejuhjKSzD4Zv43IkzY6oEbMVNcayFEUK', 'employee', 1, NOW(3), NOW(3));

-- 5. Tenant settings
INSERT INTO tenant_settings (tenant_id, setting_key, setting_value)
VALUES
  (@tenant_id, 'work_start', '09:00'),
  (@tenant_id, 'work_end', '17:00'),
  (@tenant_id, 'tolerance_minutes', '15'),
  (@tenant_id, 'overtime_mode', 'auto'),
  (@tenant_id, 'gps_default_mode', 'spesifik');

-- 6. Employee records (detail karyawan)
INSERT INTO employees (id, tenant_id, user_id, employee_code, name, email, phone, ktp_number, npwp_number, birth_date, birth_place, address, emergency_contact_name, emergency_contact_phone, join_date, employment_status, is_active, created_at, updated_at)
SELECT
  UUID(), @tenant_id, u.id, 
  CASE u.name
    WHEN 'Budi Santoso' THEN 'PIM001'
    WHEN 'Siti Rahayu' THEN 'PIM002'
    WHEN 'Ahmad Fauzi' THEN 'PIM003'
    WHEN 'Dewi Lestari' THEN 'PIM004'
    WHEN 'Rudi Hermawan' THEN 'PIM005'
    ELSE 'PIM000'
  END,
  u.name, u.email, '08' || FLOOR(1000000000 + RAND() * 9000000000), '', '', 
  DATE('1990-01-01'), 'Jakarta', 'Jl. Merdeka No. ' || FLOOR(1 + RAND() * 100), 'Keluarga', '08123456789',
  '2024-01-01', 'tetap', 1, NOW(3), NOW(3)
FROM users u
WHERE u.tenant_id = @tenant_id AND u.role = 'employee';

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
