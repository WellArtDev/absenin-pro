---
stepsCompleted: [1, 2, 3]
inputDocuments:
  - "_bmad-output/planning-artifacts/prds/prd-Absenin-2026-06-08/prd.md"
  - "_bmad-output/planning-artifacts/architecture.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-Absenin-2026-06-08/DESIGN.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-Absenin-2026-06-08/EXPERIENCE.md"
project_name: "Absenin"
date: "2026-06-08"
---

# Absenin - Epic Breakdown

## Overview

Dokumen ini menguraikan epic dan user story untuk Absenin, dari PRD (21 FRs, NFRs), Architecture (technical decisions, patterns, structure), dan UX Design (DESIGN.md + EXPERIENCE.md).

## Requirements Inventory

### Functional Requirements

**FR-1:** Karyawan dapat clock-in dengan selfie + GPS + timestamp server + device ID
**FR-2:** Karyawan dapat clock-out yang mengakhiri sesi kerja aktif
**FR-3:** Sistem validasi device — 1 karyawan 1 device, ganti device butuh approval HR
**FR-4:** Mode GPS Spesifik — verifikasi ≤10m dari koordinat klien terdaftar
**FR-5:** Mode GPS Bebas — check-in tanpa verifikasi klien spesifik
**FR-6:** Live Location — tracking real-time setiap 60 detik selama kunjungan aktif
**FR-7:** HR konfigurasi aturan lembur per tenant (Auto / Checkout→Clock-in)
**FR-8:** Auto-lembur — otomatis saat jam kerja habis, status berubah ke "Lembur"
**FR-9:** Checkout→Clock-in Lembur — karyawan checkout dulu, baru clock-in lembur
**FR-10:** Perhitungan lembur di dashboard — total jam lembur per karyawan per periode
**FR-11:** Sinkronisasi Fingerspot — baca DB lokal mesin Fingerspot, sync ke Absenin
**FR-12:** Dashboard kehadiran real-time — HR lihat ringkasan hadir/terlambat/izin/cuti
**FR-13:** Manajemen data karyawan — CRUD + import CSV + soft delete
**FR-14:** Report payroll-ready — generate laporan kehadiran per periode, export CSV
**FR-15:** Approval center — setujui/tolak cuti, izin, dispute lembur
**FR-16:** Pengajuan cuti/izin dari mobile app — pilih tipe, tanggal, alasan
**FR-17:** Notifikasi approval — FCM push ke karyawan saat disetujui/ditolak
**FR-18:** Reminder presensi — notifikasi ke karyawan yang belum absen
**FR-19:** Notifikasi status lembur — saat auto-lembur aktif atau checkout reminder
**FR-20:** Isolasi data tenant — query otomatis di-scope ke tenant_id
**FR-21:** Konfigurasi per tenant — jam kerja, aturan lembur, mode GPS, kebijakan cuti

### Non-Functional Requirements

**NFR-1 (Security):** PDO prepared statements — no raw SQL. Semua input server-side validate. CSRF web. bcrypt passwords.
**NFR-2 (Security):** AES-256-CBC encryption untuk KTP/NPWP. JWT dengan refresh token rotation. Rate limiting DB-based.
**NFR-3 (Performance):** Debounce search 300ms. Pagination 20/page. Image resize 800px JPEG. Max 100KB JSON response.
**NFR-4 (Scalability):** Portabel shared hosting → VPS. Indexing di tenant_id, date, employee_id.
**NFR-5 (Code Quality):** DRY. Max 50 LOC/function, 500 LOC/file. Helper abstractions. No duplicate code.
**NFR-6 (Maintainability):** Centralized router. Sequential SQL migrations. API versioning. File-based logging 30 hari.
**NFR-7 (Accessibility):** WCAG 2.1 AA contrast. 44px touch targets. Semantic HTML. Focus ring visible.
**NFR-8 (Battery):** GPS interval 60 detik. Wakelock hanya saat live location aktif. Tidak drop >10% per 8 jam.

### Additional Requirements (Architecture)

- **AR-1:** Project scaffold: PHP native backend (Laragon local dev) + Flutter mobile + vanilla web dashboard
- **AR-2:** API domain `api.absenin.com`, Dashboard `hub.absenin.com`
- **AR-3:** PDO base Model class dengan auto-scope tenant_id
- **AR-4:** JWT via firebase/php-jwt (Composer) — access 24h, refresh 30d
- **AR-5:** Sequential SQL migrations via `migrate.php` runner
- **AR-6:** Centralized Validator class untuk semua input validation
- **AR-7:** JSON response wrapper `{success, data/error, message, meta?}`
- **AR-8:** Naming: PHP `snake_case`, JS `camelCase`, API JSON `snake_case`
- **AR-9:** DB: UUID v4 primary keys. Every table has `tenant_id`, `created_at`, `updated_at`
- **AR-10:** File uploads: `/uploads/{tenant}/{employee}/{YYYY-MM}/` — resize 800px JPEG
- **AR-11:** Cron: fingerspot sync (5 min), send reminders (daily)
- **AR-12:** Error handling: try/catch → JSON error, no stack trace to client
- **AR-13:** Flutter: Riverpod state, go_router, dio HTTP, geolocator, camerawesome, flutter_map
- **AR-14:** Web: Leaflet.js + OSM, vanilla fetch, CSS custom properties from DESIGN.md

### UX Design Requirements

**UX-DR-1:** Implement DESIGN.md color tokens as CSS custom properties (web) and Flutter ThemeData (mobile) — primary #059669, neutral slate, text/border/semantic colors
**UX-DR-2:** Inter font family — heading + body, from Google Fonts (web) and pub package (Flutter)
**UX-DR-3:** Spacing scale 4px base — xs(4) to 6xl(64), safe area 16px mobile, max 1200px web
**UX-DR-4:** Elevation system — 4 levels (sm/md/lg/xl), semantic assignments: card=sm, modal=xl, navbar=md
**UX-DR-5:** Rounded corners — 4 scale levels (sm/md/lg/full), semantic: button=md, card=lg, input=md, badge=full
**UX-DR-6:** Button component — 4 variants: primary (green), secondary (green outline), ghost (text), danger (red). Height 44px mobile minimum
**UX-DR-7:** Input component — default state (border slate-200), focus (green ring), error (red border + helper text). Search: pill shape, gray bg, no border
**UX-DR-8:** Card component — white bg, rounded-lg, shadow-sm. Interactive variant: hover elevation + cursor pointer
**UX-DR-9:** Status badge — 4 semantik: hadir (green), terlambat (yellow), tidak hadir (red), cuti (blue). Dot + text. Pill shape
**UX-DR-10:** FAB (Floating Action Button) — green primary, 56px, rounded-xl, shadow-lg. Camera icon. Fixed bottom-right
**UX-DR-11:** Mobile bottom nav — 5 tab: Home, Riwayat, Pengajuan, Profil. Height 64px. Active: green icon+label. Inactive: gray
**UX-DR-12:** Web sidebar — 260px wide. Active item: green bg + green text. Border right. Section label caption style
**UX-DR-13:** Presensi camera flow — fullscreen camera, guide oval, GPS indicator (green/yellow/red), success: centang hijau + status auto-dismiss 3s
**UX-DR-14:** Live location banner — persistent green banner di app bar saat tracking aktif. "Live Location · {nama_klien}". Dot berkedip hijau
**UX-DR-15:** Map view (web) — Leaflet.js + OSM tiles. Pin hijau per karyawan. Popup: nama, lokasi, jam, durasi. Replay mode dengan timeline scrub
**UX-DR-16:** Approval queue (web) — list item: foto + nama + tipe + tanggal + 2 tombol. Tolak flow: modal + textarea alasan wajib. Conflict badge kuning
**UX-DR-17:** Search & filter — debounce 300ms auto-search. Web: search bar + filter chips. Mobile: sticky search + bottom sheet filter
**UX-DR-18:** Skeleton loading — placeholder abu-abu pulse animation, bukan spinner kosong. Button: disabled + inner spinner
**UX-DR-19:** Empty state — icon besar (neutral-200) + teks + CTA button. "Belum ada presensi hari ini"
**UX-DR-20:** Error handling UX — network: toast auto-retry. GPS gagal: inline warning (non-blocking). Validation: inline per field merah. Server: toast error + retry button
**UX-DR-21:** Toast notifications — slide dari atas (mobile) / top-right (web). Auto-dismiss 3 detik. Success green, error red
**UX-DR-22:** Microcopy — Indonesian. Button imperatif ("Clock In", "Setujui"). Error non-blaming. Success + next step context
**UX-DR-23:** Responsive: mobile portrait only v1 (Flutter). Web min 1024px (HR desktop). Print stylesheet untuk report CSV
**UX-DR-24:** Accessibility — WCAG AA contrast (green on white = 4.6:1 pass). 44px touch targets. Focus ring 2px green. `prefers-reduced-motion` respected

### FR Coverage Map

| FR | Epic | Description |
|----|------|-------------|
| FR-1 | Epic 3 | Clock-in dengan selfie + GPS + device |
| FR-2 | Epic 3 | Clock-out sesi kerja |
| FR-3 | Epic 2 | Validasi device (max 1, approval ganti) |
| FR-4 | Epic 3 | GPS Spesifik: verifikasi ≤10m klien |
| FR-5 | Epic 3 | GPS Bebas: tanpa verifikasi klien |
| FR-6 | Epic 3 | Live Location: tracking real-time 60s |
| FR-7 | Epic 5 | Konfigurasi aturan lembur per tenant |
| FR-8 | Epic 5 | Auto-lembur saat jam kerja habis |
| FR-9 | Epic 5 | Checkout→Clock-in Lembur |
| FR-10 | Epic 5 | Perhitungan lembur di dashboard |
| FR-11 | Epic 7 | Sinkronisasi Fingerspot (read DB) |
| FR-12 | Epic 4 | Dashboard kehadiran real-time |
| FR-13 | Epic 2 | CRUD + import CSV karyawan |
| FR-14 | Epic 4 | Report payroll-ready + export CSV |
| FR-15 | Epic 6 | Approval center: cuti/izin/dispute |
| FR-16 | Epic 6 | Pengajuan cuti/izin dari mobile |
| FR-17 | Epic 6 | Notifikasi approval (FCM) |
| FR-18 | Epic 7 | Reminder presensi harian |
| FR-19 | Epic 7 | Notifikasi status lembur |
| FR-20 | Epic 1 | Isolasi data tenant (tenant_id) |
| FR-21 | Epic 1 | Konfigurasi per tenant |

## Epic List

### Epic 1: Foundation & Multi-Tenant Setup
Setup project scaffold, database, authentication system, multi-tenant isolation, dan konfigurasi tenant — fondasi yang harus ada sebelum fitur apapun.
**FRs covered:** FR-20, FR-21
**UX covered:** UX-DR-1, UX-DR-2, UX-DR-3, UX-DR-4, UX-DR-5

### Epic 2: Manajemen Karyawan
HR dapat mengelola data karyawan (CRUD), import massal via CSV, dan mengatur device binding per karyawan.
**FRs covered:** FR-3, FR-13

### Epic 3: Presensi Mobile & GPS Tracking
Karyawan dapat clock-in/out via mobile app dengan selfie, GPS, dan device validation. Sales dapat memilih 3 mode GPS. Live location tracking selama kunjungan.
**FRs covered:** FR-1, FR-2, FR-4, FR-5, FR-6
**UX covered:** UX-DR-10, UX-DR-13, UX-DR-14

### Epic 4: Dashboard HR & Laporan
Web dashboard untuk HR: ringkasan kehadiran real-time, log presensi, report payroll-ready dengan export CSV.
**FRs covered:** FR-12, FR-14
**UX covered:** UX-DR-12, UX-DR-15, UX-DR-17, UX-DR-18, UX-DR-19, UX-DR-23

### Epic 5: Lembur & Perhitungan
HR konfigurasi aturan lembur per tenant. Sistem menghitung lembur otomatis (auto atau checkout→clock-in). Dashboard menampilkan ringkasan lembur + dispute resolution.
**FRs covered:** FR-7, FR-8, FR-9, FR-10

### Epic 6: Cuti, Izin & Approval
Karyawan mengajukan cuti/izin dari mobile app. HR menyetujui/menolak via web dashboard dengan conflict detection. Notifikasi FCM ke karyawan.
**FRs covered:** FR-15, FR-16, FR-17
**UX covered:** UX-DR-16

### Epic 7: Notifikasi & Integrasi Fingerspot
Sistem push notification (FCM) untuk reminder presensi dan status. Sinkronisasi data dari mesin fingerprint Fingerspot ke Absenin.
**FRs covered:** FR-11, FR-18, FR-19
**UX covered:** UX-DR-20, UX-DR-21, UX-DR-22

---

## Epic 1: Foundation & Multi-Tenant Setup

**Goal:** Setup project scaffold, database, authentication, multi-tenant isolation, design tokens — fondasi untuk semua fitur.

### Story 1.1: Project Scaffold & Configuration

As a developer,
I want the PHP backend project structure with config, router, composer,
So that all future features have a consistent foundation.

**Acceptance Criteria:**
**Given** Laragon running with PHP 8.x + MySQL
**When** I clone the repo and run `composer install`
**Then** I can access `api.absenin.com` and get `{"success":true,"data":"ok"}`
**And** `config.php` loads with database credentials, app URL, JWT secret
**And** `.htaccess` rewrites all requests to `public/index.php`

### Story 1.2: Database Connection & Base Model

As a developer,
I want a PDO-based base Model class with auto tenant_id scoping,
So that all queries are parameterized and tenant-scoped automatically.

**Acceptance Criteria:**
**Given** database credentials in config
**When** I call `$model = new TenantModel($tenantId)` and `$model->query("SELECT * FROM tenants")`
**Then** the query executes via PDO prepared statement
**And** results are scoped to the injected `tenant_id`
**And** `insert()` returns UUID, `update()` returns affected rows

### Story 1.3: Migration Runner & Tenant Table

As a developer,
I want a migration runner that executes sequential SQL files,
So that database schema is version-controlled and reproducible.

**Acceptance Criteria:**
**Given** SQL files in `migrations/` folder (001_tenants.sql, 002_employees.sql)
**When** I run `php migrations/migrate.php`
**Then** only un-executed migrations run in numeric order
**And** executed migrations are tracked in `migrations_log` table
**And** tenants table is created with: id (UUID), name, subdomain, is_active, timestamps

### Story 1.4: Authentication System

As a user,
I want to login with email and password,
So that I can access the system securely.

**Acceptance Criteria:**
**Given** a registered user
**When** I POST `/api/v1/auth/login` with correct credentials
**Then** I receive `{access_token, refresh_token, expires_in}` (JWT)
**When** I login via web dashboard `/dashboard/login.php`
**Then** PHP session is created with `user_id` and `tenant_id`
**And** bcrypt is used for password hashing (cost 12)
**And** JWT uses `firebase/php-jwt` library

### Story 1.5: Tenant Middleware & Isolation

As a developer,
I want every request automatically scoped to the correct tenant,
So that no tenant accidentally accesses another tenant's data.

**Acceptance Criteria:**
**Given** a JWT with `tenant_id` claim (API) or session with `tenant_id` (web)
**When** any request passes through TenantMiddleware
**Then** `$GLOBALS['tenant_id']` is set for the request lifecycle
**And** all Model queries auto-filter by `tenant_id`
**And** a request without tenant context returns 401

### Story 1.6: Tenant Settings

As a HR admin,
I want to configure company settings (work hours, overtime mode, GPS mode),
So that the system behaves according to our policies.

**Acceptance Criteria:**
**Given** I am logged in as HR
**When** I go to Settings page at `hub.absenin.com/dashboard/settings.php`
**Then** I can set: work_start, work_end, tolerance_minutes, overtime_mode (auto|checkout), gps_default_mode
**And** settings are saved to `tenant_settings` table (key-value)
**And** changes do not apply retroactively to existing sessions

### Story 1.7: Design System Tokens

As a developer,
I want DESIGN.md tokens implemented as CSS custom properties and Flutter ThemeData,
So that all UI components have consistent visual identity.

**Acceptance Criteria:**
**Given** DESIGN.md tokens defined (colors, typography, spacing, rounded, elevation)
**When** I inspect `variables.css` or Flutter `app.dart`
**Then** all color tokens are defined: primary (green-50 to green-900), neutral (slate-50 to slate-900), semantic, surface, text, border
**And** typography: Inter font family, 8-scale text sizes
**And** spacing: 4px base, xs(4) to 6xl(64)
**And** rounded: sm(4) to full(9999)
**And** elevation: sm/md/lg/xl shadow levels

---

## Epic 2: Manajemen Karyawan

**Goal:** HR dapat mengelola data karyawan — CRUD, import CSV, device binding.

### Story 2.1: Employee CRUD (API)

As a HR admin,
I want to create, read, update, and deactivate employee records via API,
So that employee data is manageable programmatically.

**Acceptance Criteria:**
**Given** I am authenticated as HR
**When** I POST `/api/v1/employees` with: companyId, employeeCode, name, email, phone, ktpNumber, npwpNumber, birthDate, birthPlace, address, emergencyContactName, emergencyContactPhone, divisionId, positionId, employmentStatus, joinDate
**Then** employee is created with UUID, leaveBalance=12, isActive=true
**And** KTP/NPWP are AES-256 encrypted in database
**When** I GET `/api/v1/employees?page=1&limit=20`
**Then** paginated list returns with `{data, meta: {page, limit, total}}`
**When** I PUT `/api/v1/employees/{id}` with updated fields
**Then** employee record is updated, `updatedAt` refreshed
**When** I DELETE `/api/v1/employees/{id}`
**Then** `isActive` is set to false (soft delete)

### Story 2.2: Employee Web Dashboard

As a HR admin,
I want to manage employees from a web dashboard,
So that I can do all employee management without API tools.

**Acceptance Criteria:**
**Given** I am logged in at `hub.absenin.com`
**When** I click "Karyawan" in sidebar
**Then** I see a table: name, NIK, division, position, status (active/inactive)
**And** search bar with 300ms debounce filters the table
**And** I can click "Tambah" to open a form with all employee fields
**And** I can click edit icon to modify existing employee
**And** I can click deactivate to soft-delete (confirmation dialog)

### Story 2.3: Employee Import CSV

As a HR admin,
I want to import multiple employees from a CSV file,
So that onboarding a new company is fast.

**Acceptance Criteria:**
**Given** I am on the Employees page
**When** I click "Import CSV" and select a valid CSV file
**Then** system validates headers match employee fields
**And** valid rows are inserted, errors are reported per row
**And** duplicate employeeCode is rejected with error message
**And** success toast: "X karyawan berhasil diimpor, Y gagal"

### Story 2.4: Device Binding

As a HR admin,
I want each employee limited to 1 registered device,
So that titip absen is prevented.

**Acceptance Criteria:**
**Given** employee has no registered device
**When** they first install and login via mobile app
**Then** device ID is registered and stored in `devices` table (employee_id, device_id, is_active)
**Given** employee wants to change device
**When** they tap "Ganti Perangkat" in mobile app
**Then** a change request is sent to HR approval queue
**When** HR approves the request
**Then** old device is deactivated, new device is registered
**And** employee can now clock-in from new device

---

## Epic 3: Presensi Mobile & GPS Tracking

**Goal:** Karyawan clock-in/out via mobile app dengan selfie, GPS, dan device validation. Sales bisa 3 mode GPS + live location.

### Story 3.1: Flutter App Init & Navigation

As a developer,
I want a Flutter app with Material theme and bottom navigation,
So that all screens have a consistent shell.

**Acceptance Criteria:**
**Given** `flutter create absenin_mobile`
**When** I run the app
**Then** ThemeData matches DESIGN.md: primary=#059669, font=Inter, spacing/rounded/elevation tokens
**And** bottom navigation has 4 tabs: Home, Riwayat, Pengajuan, Profil
**And** go_router handles screen transitions
**And** Dio HTTP client is configured with base URL + JWT interceptor

### Story 3.2: Login Screen (Mobile)

As an employee,
I want to login with email and password on the mobile app,
So that I can access my attendance features.

**Acceptance Criteria:**
**Given** I open the app for the first time
**When** I enter email and password and tap "Masuk"
**Then** JWT tokens are received and stored securely
**And** device ID is registered (first-time only)
**And** I am navigated to Home screen
**Given** my token is about to expire (5 min before)
**When** any API call is made
**Then** dio interceptor auto-refreshes the token silently

### Story 3.3: Clock-In with Selfie + GPS

As an employee,
I want to clock-in by taking a selfie with GPS capture,
So that my attendance is verified with photo and location.

**Acceptance Criteria:**
**Given** I am on Home screen and not clocked-in
**When** I tap the FAB (camera icon)
**Then** fullscreen camera opens (camerawesome, no gallery picker)
**And** GPS indicator shows status: green (accurate), yellow (searching), red (unavailable)
**When** I capture selfie, then tap "Kirim"
**Then** image is compressed to 800px JPEG, uploaded with GPS + timestamp + device ID
**And** API returns `{session_id, status: "hadir", timestamp}`
**And** success screen shows: green checkmark + "Presensi berhasil. Hadir · 08:50"

### Story 3.4: Clock-Out

As an employee,
I want to clock-out to end my work session,
So that my working hours are accurately recorded.

**Acceptance Criteria:**
**Given** I have an active attendance session
**When** I tap "Clock-out" on Home screen
**Then** session is ended, duration calculated
**And** if past normal work hours, overtime component is flagged (see Epic 5)
**And** success confirmation shows total duration

### Story 3.5: GPS Mode Spesifik (Client Verification)

As a sales employee,
I want to check-in at a specific client location with GPS verification,
So that my manager knows I actually visited the client.

**Acceptance Criteria:**
**Given** I tap FAB and capture selfie
**When** I select mode "Spesifik" and choose a client from the list
**Then** system verifies my GPS ≤ 10m from client's registered coordinates
**When** GPS is within radius → green checkmark, "Mulai Kunjungan" button
**When** GPS is outside radius → warning + "Laporkan Masalah" button
**And** laporkan masalah sends GPS actual + reason to HR

### Story 3.6: GPS Mode Bebas

As an employee,
I want to check-in with GPS recorded but without client verification,
So that I can log my location flexibly.

**Acceptance Criteria:**
**Given** I am on the presensi screen
**When** I select mode "Bebas"
**Then** GPS is recorded but no radius verification is done
**And** I can tap "Mulai Kunjungan" or just "Clock In"
**And** my location appears on the HR map dashboard

### Story 3.7: Live Location Tracking

As a sales employee in active visit,
I want my location tracked in real-time,
So that my manager can see my movement during client visits.

**Acceptance Criteria:**
**Given** I started a kunjungan (Spesifik or Bebas mode)
**When** live location is active
**Then** GPS coordinates are sent to server every 60 seconds
**And** a green banner shows "Live Location · {client_name}" with blinking dot
**When** I tap "Akhiri Kunjungan"
**Then** live tracking stops, banner disappears
**And** track points are saved for replay in HR dashboard
**And** background service keeps tracking even if app is minimized

### Story 3.8: Mobile Home Screen & FAB

As an employee,
I want a home screen showing my attendance status and quick actions,
So that I can see my status at a glance and clock-in quickly.

**Acceptance Criteria:**
**Given** I am logged in and not clocked-in
**When** I see Home screen
**Then** status card shows "Belum Presensi" with time
**And** FAB (green circle, camera icon, 56px) is visible at bottom-right
**Given** I am clocked-in
**When** I see Home screen
**Then** status card shows "Hadir · 08:50" with elapsed time
**And** FAB changes to clock-out button
**And** if overtime mode is active, status shows "Lembur · sejak 17:00"

---

## Epic 4: Dashboard HR & Laporan

**Goal:** Web dashboard untuk HR — ringkasan kehadiran real-time, log, map view, report payroll-ready CSV.

### Story 4.1: Web Dashboard Layout

As a HR admin,
I want a web dashboard with sidebar navigation,
So that I can access all HR functions easily.

**Acceptance Criteria:**
**Given** I login at `hub.absenin.com`
**When** the dashboard loads
**Then** sidebar (260px) shows: Dashboard, Karyawan, Presensi, Lembur, Cuti & Izin, Report, Klien, Pengaturan
**And** active menu item has green background + green text
**And** top bar shows company name + current time + user avatar
**And** content area has max-width 1200px

### Story 4.2: Dashboard Home (Real-time Stats)

As a HR admin,
I want a real-time dashboard showing today's attendance summary,
So that I know who's present, late, or absent at a glance.

**Acceptance Criteria:**
**Given** I am on the Dashboard Home
**When** the page loads and auto-refreshes every 60s
**Then** I see stat cards: Hadir (green), Terlambat (yellow), Izin (blue), Cuti (blue), Belum Absen (gray)
**And** employee list with: avatar, name, position, status badge, clock-in time
**And** filter tabs: Semua | Hadir | Terlambat | Izin | Cuti
**And** last refresh indicator: "🔄 30 detik lalu" with manual refresh button

### Story 4.3: Attendance Log (Search & Filter)

As a HR admin,
I want to browse and search attendance logs,
So that I can investigate specific days or employees.

**Acceptance Criteria:**
**Given** I click "Presensi" in sidebar
**When** I select a date range and/or search employee name
**Then** debounced search (300ms) filters results
**And** results show in a table: date, employee, clock-in, clock-out, duration, status, source (mobile/fingerprint)
**And** pagination shows 20 per page
**When** I click a row
**Then** detail expands: selfie thumbnail, GPS map, device info

### Story 4.4: Map View (Live & Replay)

As a HR admin,
I want to see field employees' locations on a live map,
So that I can verify their whereabouts during work hours.

**Acceptance Criteria:**
**Given** I click on a location-enabled employee or go to map view
**When** the map loads (Leaflet.js + OSM tiles)
**Then** green pins show employees with active live location
**And** clicking a pin shows popup: name, client/location, start time, duration
**And** for past visits: I can click "Replay" to see the track line with timeline scrub

### Story 4.5: Report Payroll (Generate + Preview)

As a HR admin,
I want to generate attendance reports for a date range,
So that I can prepare payroll data quickly.

**Acceptance Criteria:**
**Given** I click "Report" in sidebar
**When** I select period (start date - end date) and generate
**Then** preview table shows per employee: name, NIK, work days, normal hours, overtime hours, leave days, permission days, sick days
**And** subtotals at the bottom
**And** dispute flags are visible if unresolved

### Story 4.6: Report CSV Export

As a HR admin,
I want to export the report as CSV,
So that I can import it into payroll software.

**Acceptance Criteria:**
**Given** I have generated a report
**When** I click "Export CSV"
**Then** a CSV file downloads with headers: Nama, NIK, Hari Kerja, Jam Normal, Jam Lembur, Cuti, Izin, Sakit
**And** data is correctly encoded for Excel (UTF-8 BOM)
**And** filename format: `absenin-report-{company}-{start}-{end}.csv`

---

## Epic 5: Lembur & Perhitungan

**Goal:** HR konfigurasi aturan lembur. Sistem menghitung otomatis (auto / checkout→clock-in). Dashboard lembur + dispute.

### Story 5.1: Overtime Settings

As a HR admin,
I want to configure overtime mode per company,
So that the system matches our overtime policy.

**Acceptance Criteria:**
**Given** I am on Settings page
**When** I select overtime mode: "Auto" or "Checkout → Clock-in Lembur"
**Then** the setting is saved to `tenant_settings.overtime_mode`
**And** if "Checkout" mode, I can also set grace period (default 30 min)
**And** changes apply to new work sessions only, not retroactive

### Story 5.2: Auto-Overtime Trigger

As a technician working past normal hours,
I want overtime to activate automatically when work hours end,
So that I don't need to think about checking out and in again.

**Acceptance Criteria:**
**Given** tenant overtime mode = "Auto"
**When** the current time reaches `work_end_time` (e.g., 17:00)
**And** the employee has an active attendance session
**Then** session status changes from "hadir" to "lembur"
**And** FCM notification: "Jam kerja normal selesai. Anda dalam status Lembur."
**And** all time after 17:00 is calculated as overtime

### Story 5.3: Checkout → Clock-in Lembur Flow

As an employee,
I want to checkout first, then clock-in for overtime separately,
So that my normal hours and overtime are clearly separated.

**Acceptance Criteria:**
**Given** tenant overtime mode = "Checkout → Clock-in Lembur"
**When** `work_end_time` is reached
**Then** FCM notification: "Jam kerja selesai. Silakan checkout."
**And** employee must checkout within grace period (e.g., 30 min)
**Given** employee has checked out after work hours
**When** they tap FAB and clock-in again
**Then** new session is tagged "lembur" 
**And** overtime hours = clock_out_lembur - clock_in_lembur
**And** if employee doesn't checkout within grace period → flag `checkout_missed`, HR notified

### Story 5.4: Overtime Dashboard

As a HR admin,
I want to see overtime summary per employee per period,
So that I can review and approve overtime data.

**Acceptance Criteria:**
**Given** I click "Lembur" in sidebar
**When** I select a period
**Then** table shows per employee: name, normal hours, overtime hours, overtime sessions count
**And** I can click an employee to see detail: date-by-date breakdown
**And** total overtime hours are displayed at bottom

### Story 5.5: Overtime Dispute

As an employee,
I want to flag incorrect overtime calculation,
So that HR can review and fix before payroll.

**Acceptance Criteria:**
**Given** I see my overtime summary in the mobile app
**When** I tap "Dispute" on a specific day's overtime entry
**Then** dispute is created with reason (text field)
**And** HR sees dispute flag in overtime dashboard + approval queue
**When** HR reviews and resolves (accepts employee's version or keeps original)
**Then** dispute is resolved, employee gets notification
**And** resolved disputes reflect in report CSV

---

## Epic 6: Cuti, Izin & Approval

**Goal:** Karyawan ajukan cuti/izin dari mobile. HR approve/reject via web dengan conflict detection.

### Story 6.1: Leave Request Form (Mobile)

As an employee,
I want to submit leave or permission requests from the mobile app,
So that I don't need to fill paper forms.

**Acceptance Criteria:**
**Given** I am on "Pengajuan" tab
**When** I tap "Ajukan" and select type: Cuti / Izin / Sakit
**Then** form shows: date picker (range for multi-day), reason textarea (required), submit button
**When** I submit
**Then** request is created with status "pending"
**And** success toast: "Pengajuan dikirim. Menunggu approval."

### Story 6.2: Leave Request Status (Mobile)

As an employee,
I want to see the status of my leave requests,
So that I know if my leave was approved.

**Acceptance Criteria:**
**Given** I am on "Pengajuan" tab
**When** I view the list
**Then** each item shows: type, dates, status badge (pending/approved/rejected), created date
**When** I tap an item
**Then** detail shows: full info + rejection reason if rejected
**And** approved requests show green badge, rejected show red

### Story 6.3: Approval Queue (Web)

As a HR admin,
I want to see a queue of pending leave requests,
So that I can process approvals efficiently.

**Acceptance Criteria:**
**Given** I click "Cuti & Izin" in sidebar
**When** the approval queue loads
**Then** each item shows: employee photo, name, type, dates, reason preview, "Setujui" (green) + "Tolak" (red outline) buttons
**And** pending count badge on sidebar menu
**And** conflict detection badge (yellow "⚠️ Bentrok") if applicable

### Story 6.4: Approve / Reject with Reason

As a HR admin,
I want to approve or reject leave requests with optional reason,
So that employees know the outcome clearly.

**Acceptance Criteria:**
**Given** I click "Setujui" on a leave request
**Then** request status changes to "approved", employee gets FCM notification
**And** leave balance is decremented (for cuti type)
**Given** I click "Tolak"
**Then** a modal opens with reason textarea (required)
**When** I submit the rejection
**Then** employee gets FCM notification: "Cuti ditolak. Alasan: {reason}"

### Story 6.5: Conflict Detection

As a HR admin,
I want the system to warn me if approving a leave creates a staffing conflict,
So that I don't accidentally leave a department understaffed.

**Acceptance Criteria:**
**Given** multiple employees in the same division
**When** two or more submit leave for overlapping dates
**Then** each conflicting request shows a yellow badge "⚠️ Bentrok dengan {name}"
**And** HR can still approve despite conflict (override)
**And** no auto-rejection — HR always makes the final call

### Story 6.6: Leave Balance

As an employee,
I want to see my remaining leave balance,
So that I know how many days I can still take.

**Acceptance Criteria:**
**Given** I am on "Pengajuan" tab
**When** I view the tab
**Then** my leave balance is displayed: "{X} hari tersisa" (default 12)
**When** a cuti is approved
**Then** balance is decremented by the number of days approved
**And** HR can manually adjust balance in settings

---

## Epic 7: Notifikasi & Integrasi Fingerspot

**Goal:** Push notifications via FCM + sinkronisasi data dari mesin Fingerspot.

### Story 7.1: FCM Token Registration

As a developer,
I want the mobile app to register an FCM token on login,
So that push notifications can be sent to the device.

**Acceptance Criteria:**
**Given** employee logs in on mobile app
**When** login succeeds
**Then** FCM device token is sent to `POST /api/v1/devices/register`
**And** token is stored in `notifications` table (employee_id, fcm_token, platform, is_active)
**When** employee logs out
**Then** token is deactivated (is_active = false)

### Story 7.2: Attendance Reminder Notification

As an employee,
I want a reminder notification if I haven't clocked-in,
So that I don't forget to record my attendance.

**Acceptance Criteria:**
**Given** reminder time is configured (default: 30 min before work_start)
**When** cron job `send_reminders.php` runs
**Then** FCM notification is sent to employees who haven't clocked-in today
**And** notification text: "Jangan lupa presensi! Jam masuk: 09:00"
**And** tapping notification opens the app to Home screen

### Story 7.3: Approval Status Notification

As an employee,
I want to be notified when my leave request is approved or rejected,
So that I know immediately without checking the app.

**Acceptance Criteria:**
**Given** HR approves my leave request
**When** approval is saved
**Then** FCM notification: "Cuti 15-16 Juli disetujui."
**Given** HR rejects my leave request
**Then** FCM notification: "Cuti 15-16 Juli ditolak. Alasan: ..."
**And** tapping notification opens the app to Pengajuan detail

### Story 7.4: Overtime Status Notification

As an employee in overtime mode,
I want to receive status change notifications,
So that I know when my normal hours end and overtime starts.

**Acceptance Criteria:**
**Given** auto-overtime mode and current time = work_end
**When** session status changes to "lembur"
**Then** FCM notification: "Jam kerja normal selesai. Anda dalam status Lembur."
**Given** checkout→clock-in mode and current time = work_end
**Then** FCM notification: "Jam kerja selesai. Silakan checkout."
**When** 30 min grace period passes without checkout
**Then** notification: "Anda belum checkout. Segera checkout."

### Story 7.5: Fingerspot Database Sync

As a developer,
I want to read attendance data from Fingerspot's MySQL database,
So that fingerprint attendance syncs into Absenin automatically.

**Acceptance Criteria:**
**Given** Fingerspot machine has local MySQL with table `att_log`
**When** sync script connects (read-only, config per tenant)
**Then** new records since last sync are fetched
**And** mapped to: employee (by configured employee_id mapping), timestamp, status (0=checkin, 1=checkout)
**When** mapping fails (unknown employee)
**Then** record is logged as `unmapped` for HR to resolve manually

### Story 7.6: Fingerspot Cron Schedule

As a developer,
I want Fingerspot sync to run automatically every 5 minutes,
So that fingerprint data is near real-time in Absenin.

**Acceptance Criteria:**
**Given** `cron/fingerspot_sync.php` exists
**When** the cron job fires every 5 minutes
**Then** sync runs for all active tenants with Fingerspot configured
**And** sync results are logged: synced_count, unmapped_count, error_count
**And** errors are logged to `logs/fingerspot-YYYY-MM-DD.log`
**And** successive failures trigger a warning (not spam — once per hour)
