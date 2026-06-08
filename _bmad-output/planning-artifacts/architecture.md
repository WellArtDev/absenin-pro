---
stepsCompleted: [1, 2, 3, 4, 5, 6]
inputDocuments:
  - "_bmad-output/planning-artifacts/briefs/brief-Absenin-2026-06-08/brief.md"
  - "_bmad-output/planning-artifacts/prds/prd-Absenin-2026-06-08/prd.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-Absenin-2026-06-08/DESIGN.md"
  - "_bmad-output/planning-artifacts/ux-designs/ux-Absenin-2026-06-08/EXPERIENCE.md"
workflowType: 'architecture'
project_name: 'Absenin'
user_name: 'WellArtDev'
date: '2026-06-08'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements (21 FRs):**
- **Presensi Mobile** (FR-1–3): selfie + GPS + device validation. Flutter camera, GPS real-time.
- **Mode GPS** (FR-4–6): 3 mode (Spesifik, Bebas, Live Location). Map rendering, coordinate verification ≤10m.
- **Lembur** (FR-7–10): 2 mode per tenant (auto / checkout→clock-in). Durasi kalkulasi server-side.
- **Fingerspot** (FR-11): sync dari database lokal MySQL Fingerspot. Cron-based, read-only.
- **Dashboard HR** (FR-12–15): real-time polling, data CRUD, CSV export, approval workflow.
- **Cuti/Izin** (FR-16–17): form pengajuan mobile + FCM notification + approval queue.
- **Notifikasi** (FR-18–19): FCM push. Reminder presensi, status approval.
- **Multi-Tenant** (FR-20–21): isolasi row-level (tenant_id). Konfigurasi per tenant.

**Non-Functional Requirements:**
- **Security**: PDO prepared statements, CSRF tokens, bcrypt, JWT mobile, input validation server-side mandatory, rate limiting, AES-256 untuk KTP/NPWP.
- **Performance**: debounce 300ms search, pagination 20/page, image resize 800px, cron-based async (no queue), max 100KB JSON response.
- **Scalability**: portabel shared hosting → VPS. Indexing di tenant_id, date, employee_id. File uploads di filesystem.
- **Code Quality**: DRY, max 50 LOC/function, max 500 LOC/file. Helper abstractions untuk validasi, formatting, API response.
- **Maintainability**: centralized router, config file, SQL migrations sequential, API versioning `/api/v1/`.

### Scale & Complexity

- Primary domain: **Full-stack** — PHP REST API + Flutter mobile + vanilla HTML/CSS web
- Complexity level: **Medium-High**
- Estimated architectural components: 5 (REST API, Web Dashboard, Mobile App, Cron Scheduler, Database)
- Multi-tenant: Yes (row-level isolation)
- Real-time features: Polling-based (60s interval), GPS live tracking
- File storage: Selfie images + CSV exports

### Technical Constraints & Dependencies

- **Hosting**: cPanel shared hosting (Apache, PHP 8.x, MySQL). No Docker, Redis, WebSocket, Node.js.
- **Map**: OpenStreetMap (Leaflet.js web, flutter_map mobile). No API key.
- **Push**: Firebase Cloud Messaging. Free tier.
- **Auth**: JWT (mobile) + PHP sessions (web).
- **Fingerspot**: MySQL direct connection (read-only), table `att_log`.
- **Migration path**: Shared hosting → VPS. Kode harus portabel.
- **No PHP framework**: Struktur manual — `controllers/`, `models/`, `views/`, `helpers/`, `api/`, `cron/`.

### Cross-Cutting Concerns Identified

1. **Multi-tenancy isolation**: setiap query di-scope ke `tenant_id`. Auth layer menyuntikkan tenant context.
2. **Auth dual-surface**: JWT mobile + session web — token lifecycle, refresh, invalidation.
3. **API Design**: RESTful untuk Flutter app + internal API untuk web dashboard. Versioning, rate limiting, error format konsisten.
4. **File management**: selfie upload → resize → filesystem storage. Path: `/uploads/{tenant}/{employee}/{date}/`.
5. **Cron scheduling**: Fingerspot sync (5 min), notifikasi reminder. Fallback untuk shared hosting (no systemd/Redis).
6. **State synchronization**: polling (60s) vs real-time — acceptable latency trade-off untuk shared hosting constraint.
7. **Database design**: indexing strategy, migrations, tenant-scoped queries, encryption untuk KTP/NPWP.

## Starter Template Evaluation

### Primary Technology Domain
Full-stack — PHP REST API backend + Flutter mobile + vanilla web dashboard. No CLI starter applicable (PHP native, no framework). Manual scaffold.

### Selected Setup: Manual Project Scaffold

**Rationale:** User memilih PHP native (no Laravel/CI/Slim). Flutter via `flutter create`. Web dashboard manual HTML/CSS/JS.

**Backend Structure:**
```
/
├── public/            # Web root
│   ├── index.php      # Front controller
│   └── .htaccess      # URL rewrite
├── app/
│   ├── controllers/
│   ├── models/        # PDO models
│   ├── helpers/       # validate, format, api_response, db
│   └── middleware/    # auth, tenant_scope, csrf
├── api/v1/            # REST → Flutter
├── views/             # Dashboard templates
├── cron/              # Fingerspot sync, reminders
├── config/
│   ├── config.php
│   └── database.php
├── migrations/        # Sequential SQL
└── uploads/           # {tenant}/{employee}/{date}/
```

**Mobile (Flutter):**
```
flutter create absenin_mobile
lib/
├── app.dart           # MaterialApp + ThemeData
├── config/            # API URL, constants
├── models/            # Data classes
├── services/          # API client, auth, GPS, camera
├── screens/           # 6 surfaces
├── widgets/           # fab, card, badge, map_view
└── providers/         # State (Provider/Riverpod)
```

**Web Dashboard:**
```
public/dashboard/
├── index.php
├── assets/
│   ├── css/variables.css  # DESIGN.md tokens
│   ├── css/app.css
│   ├── js/api.js          # fetch wrapper
│   ├── js/map.js          # Leaflet
│   └── js/app.js
└── components/            # sidebar, header, tables
```

**Init Commands:**
```bash
mkdir -p public app/{controllers,models,helpers,middleware} api/v1 views cron config migrations uploads
mkdir -p public/dashboard/assets/{css,js,img} public/dashboard/components
flutter create absenin_mobile
```

**Dev Experience:** Local: Laragon (`C:\laragon`) — Apache + MySQL + PHP 8.x. Flutter hot reload (sub-second), browser refresh (web). Testing: PHPUnit + Flutter test.

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
- Row-level multi-tenancy via `tenant_id`
- JWT auth for mobile + PHP sessions for web
- PDO prepared statements (no raw SQL)
- Server-side input validation mandatory
- RESTful JSON API with standardized response format
- AES-256-CBC encryption for KTP/NPWP

**Important Decisions (Shape Architecture):**
- Custom base Model class for all DB queries
- Sequential SQL migrations with manual runner
- FCM for push notifications (free tier)
- Riverpod state management (Flutter)
- Vanilla JavaScript for web dashboard (no framework)
- File-based logging, rotation 30 hari

**Deferred Decisions (Post-MVP):**
- Queue system (Redis/Gearman) → cron-based fallback v1
- Error tracking (Sentry) → file-based logs v1
- CI/CD pipeline → manual deploy v1
- API key authentication → JWT sufficient v1

### Data Architecture

| Decision | Choice | Version | Rationale |
|----------|--------|---------|-----------|
| Database | MySQL/MariaDB | cPanel default | Shared hosting default. Adequate for scale. |
| Query layer | PDO + custom base `Model` class | PHP 8.x built-in | All queries via `$model->query()`, `$model->insert()`. Auto-scope `tenant_id` in base class. |
| Migrations | Sequential SQL + `migrate.php` runner | Manual | Scan `migrations/` folder, execute by prefix number, track in `migrations_log` table. |
| Validation | Server-side `Validator` class | Manual | `Validator::required()`, `Validator::email()`, `Validator::min()`. Returns errors array. |
| Encryption | AES-256-CBC `openssl_encrypt()` | PHP built-in | For KTP/NPWP. Key from config. Encrypt on write, decrypt on read. |
| File storage | Filesystem: `/uploads/{tenant}/{employee}/{date}/` | N/A | Selfie images. Resize to 800px JPEG before store. |
| Export | CSV via `fputcsv()` | PHP built-in | Payroll report, employee list. |

### Authentication & Security

| Decision | Choice | Version | Rationale |
|----------|--------|---------|-----------|
| Mobile auth | JWT via `firebase/php-jwt` | Latest stable | One Composer dependency. Well-maintained, de facto PHP JWT lib. |
| Token lifecycle | Access: 24h, Refresh: 30d | N/A | Auto-refresh via Flutter Dio interceptor. |
| Web auth | PHP native sessions | PHP 8.x | Session-based. CSRF token per form. |
| Password | bcrypt via `password_hash()` | PHP built-in | Cost factor 12. Default PHP password API. |
| Rate limiting | DB table `rate_limits` | N/A | Key: `{ip}:{endpoint}:{minute}`. Reset via cron. No Redis needed. |
| Input filter | `filter_var()` + `htmlspecialchars()` | PHP built-in | Server-side mandatory. NEVER trust client-side only. |
| XSS | `htmlspecialchars()` + CSP header | N/A | Output escaping mandatory. Content-Security-Policy header. |
| CSRF | Token per form/session | N/A | Web forms only. API uses JWT (CSRF-free). |

### API & Communication Patterns

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Style | RESTful JSON | Standard. Flutter `dio` client simple. |
| Response format | `{ success, data?, error?, meta? }` | Konsisten di semua endpoint. Flutter type-safe parsing. |
| Error codes | HTTP status + internal `code` | `401 "token_expired"`, `422 "validation_failed"`, `429 "rate_limited"` |
| Pagination (mobile) | Cursor-based: `?after=ID&limit=20` | Idempotent across data changes. Better for infinite scroll. |
| Pagination (web) | Offset: `?page=1&limit=20` | Standard table pagination. |
| Versioning | URL prefix `/api/v1/` | Explicit. Future v2 parallel. |
| Docs | `api/v1/README.md` | Manual markdown. No Swagger overhead. |
| CORS | cPanel config per domain | `api.absenin.com` allow `hub.absenin.com` + Flutter origin. |
| Fetch wrapper (web) | `api.js` module | `api.get('/employees')`, `api.post('/presensi', body)`. Auto-attach auth header. |
| HTTP client (Flutter) | `dio` | Interceptor: JWT refresh, retry, timeout 30s, error mapping. |

### Frontend Architecture

#### Mobile (Flutter)

| Decision | Choice | Rationale |
|----------|--------|-----------|
| State management | Riverpod | Modern, testable, auto-dispose. Better than Provider for real-time GPS. |
| Routing | `go_router` | Declarative, type-safe, deep linking. |
| HTTP client | `dio` | JWT interceptor, retry, timeout. |
| GPS | `geolocator` + `geocoding` | De facto Flutter GPS plugins. |
| Background GPS | `flutter_background_service` | Live location tracking saat app di background. |
| Camera | `camerawesome` | Custom UI, no gallery picker. Prevent upload from gallery. |
| Map | `flutter_map` + OpenStreetMap | Free tiles, no API key. |
| Image resize | `flutter_image_compress` | Compress selfie to 800px JPEG quality 80% before upload. |
| Theme | ThemeData from DESIGN.md tokens | Single Material Theme. Colors, typography, spacing from DESIGN.md. |

#### Web Dashboard

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Framework | None — vanilla HTML/CSS/JS | Shared hosting compatible. No build step. |
| CSS | Custom properties (`variables.css`) | Token dari DESIGN.md. Consistent with Flutter ThemeData. |
| Map | Leaflet.js + OSM | Free. Markers, popups, replay track. |
| Charts | None v1 (text-based reports) | v2 bisa Chart.js jika perlu visual grafik. |
| Table | Vanilla + fetch + innerHTML | Simple. Maybe `datatables.net` v2 jika butuh sort/filter client-side. |
| Notifikasi browser | Notification API | Untuk approval pending. Fallback: badge di sidebar menu. |

### Infrastructure & Deployment

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Local dev | Laragon (`C:\laragon`) | Apache + MySQL + PHP 8.x. Full LAMP stack locally. |
| API domain | `api.absenin.com` | Subdomain dedicated untuk REST API. |
| Dashboard | `hub.absenin.com` | Subdomain untuk web dashboard HR. |
| Production | cPanel shared hosting → VPS later | cPanel credentials to be provided. Git-based deploy. |
| Deploy method | Git pull + manual file copy | Via cPanel Git Version Control or FTP. |
| Logging | File-based: `logs/app-YYYY-MM-DD.log` | Rotasi 30 hari. `error_log()` + custom Logger class. |
| Backup | cPanel Backup Wizard | Manual. mysqldump for additional safety. |
| Monitoring | PHP `error_log` only v1 | No external monitoring. cPanel errors log. |
| SSL | Let's Encrypt via cPanel | Free, auto-renew. |

## Implementation Patterns & Consistency Rules

### Naming Conventions

| Scope | Convention | Example |
|-------|-----------|---------|
| PHP functions/vars | `snake_case` | `get_user_by_id()`, `$employee_code` |
| PHP classes | `PascalCase` | `UserController`, `DatabaseModel` |
| PHP files (class) | `PascalCase.php` | `UserController.php` |
| PHP files (non-class) | `snake_case.php` | `config.php`, `helpers.php` |
| DB tables | `snake_case` plural | `employees`, `attendance_logs` |
| DB columns | `snake_case` | `employee_id`, `created_at` |
| JSON keys (API) | `snake_case` | `{ "employee_code": "EMP001" }` |
| JS variables (dashboard) | `camelCase` | `const employeeList = data.employees` |
| Dart vars (Flutter) | `camelCase` | `final employeeName` |
| Dart classes (Flutter) | `PascalCase` | `UserService`, `AttendanceProvider` |
| Dart files (Flutter) | `snake_case.dart` | `user_service.dart`, `home_screen.dart` |
| API endpoints | `snake_case` plural | `/api/v1/attendance-logs` |

### API Response Formats

**ALL endpoints MUST use this wrapper:**

```json
// Success (collection)
{"success": true, "data": [...], "meta": {"page": 1, "limit": 20, "total": 143}}

// Success (single)
{"success": true, "data": {"id": "...", "name": "..."}}

// Error
{"success": false, "error": "validation_failed", "message": "Email wajib diisi"}
```

### Database Patterns

**Base Model (ALL queries):**
```php
abstract class Model {
    protected string $tenantId;
    public function __construct(string $tenantId) { $this->tenantId = $tenantId; }
    protected function query(string $sql, array $params = []): array { /* PDO */ }
    protected function insert(string $table, array $data): string { /* returns UUID */ }
    protected function update(string $table, array $data, array $where): int { /* affected */ }
}
```

**Every table:** `id TEXT NOT NULL PRIMARY KEY` (UUID v4), `tenant_id TEXT NOT NULL`, `created_at TIMESTAMP(3)`, `updated_at TIMESTAMP(3)`.

### Auth Patterns

**Web:** session check → redirect login if `!isset($_SESSION['user_id'])`. CSRF token per form.

**Mobile (JWT):** Login → `{access_token, refresh_token, expires_in}`. Dio interceptor auto-refresh 5 min before expiry. POST `/api/v1/auth/refresh`.

### Error Handling

| Layer | Pattern |
|-------|---------|
| PHP | try/catch → JSON error (no stack trace to client). Log to file. |
| Flutter | Dio interceptor → typed exceptions → `ErrorMessages` class (Indonesian) |
| Web | `fetch()` `.catch()` → toast notification. 401 → login redirect. |

### Date/Time

| Layer | Format | Example |
|-------|--------|---------|
| API JSON | ISO 8601 | `"2026-06-08T09:15:00+07:00"` |
| Database | `TIMESTAMP(3)` UTC | MySQL UTC |
| Display | Indonesian locale | `08 Juni 2026, 09:15 WIB` |

### Enforcement Rules

**ALL agents MUST:**
1. NEVER raw SQL — always PDO prepared statements
2. NEVER return raw error to client
3. ALWAYS use `{success, data/error, message}` wrapper
4. ALWAYS auto-scope `tenant_id` from auth context
5. NEVER hardcode URLs — use `config.php` constants
6. NEVER commit `.env` or credentials
7. ALWAYS server-side validate (client-side = UX only)
8. ALWAYS `snake_case` JSON keys (match DB columns)

## Project Structure & Boundaries

### Complete Backend Tree (PHP + Web Dashboard)

```
absenin/
├── .gitignore
├── .htaccess                         # Deny all except public/
├── README.md
├── composer.json
│
├── public/                           # Web root
│   ├── index.php                     # API front controller (api.absenin.com)
│   ├── .htaccess                     # Rewrite /api/v1/* → index.php
│   │
│   ├── dashboard/                    # Web HR (hub.absenin.com)
│   │   ├── index.php
│   │   ├── .htaccess
│   │   ├── assets/
│   │   │   ├── css/variables.css     # DESIGN.md → CSS custom properties
│   │   │   ├── css/app.css
│   │   │   ├── js/api.js             # fetch wrapper, auth header
│   │   │   ├── js/map.js             # Leaflet + OSM
│   │   │   └── js/app.js             # Dashboard logic
│   │   └── views/
│   │       ├── login.php             # Auth surface
│   │       ├── home.php              # FR-12 Dashboard real-time
│   │       ├── employees.php         # FR-13 Manajemen karyawan
│   │       ├── attendance.php        # FR-12 Log presensi
│   │       ├── overtime.php          # FR-10 Lembur list + dispute
│   │       ├── leave.php             # FR-15 Approval cuti/izin
│   │       ├── report.php            # FR-14 Report payroll
│   │       ├── clients.php           # FR-4 Klien + GPS config
│   │       └── settings.php          # FR-7,21 Tenant settings
│   │
│   └── uploads/                      # {tenant}/{employee}/{YYYY-MM}/
│
├── app/
│   ├── controllers/                  # Request → Process → Response
│   │   ├── AuthController.php
│   │   ├── AttendanceController.php  # FR-1,2,3
│   │   ├── LocationController.php    # FR-4,5,6
│   │   ├── OvertimeController.php    # FR-8,9,10
│   │   ├── EmployeeController.php    # FR-13
│   │   ├── LeaveController.php       # FR-16
│   │   ├── ApprovalController.php    # FR-15
│   │   ├── ClientController.php      # FR-4
│   │   ├── ReportController.php      # FR-14
│   │   ├── NotificationController.php # FR-18,19
│   │   └── SettingsController.php    # FR-7,21
│   │
│   ├── models/                       # PDO queries via base Model
│   │   ├── Model.php                 # Base: query(), insert(), update()
│   │   ├── Tenant.php
│   │   ├── Employee.php
│   │   ├── AttendanceSession.php
│   │   ├── LocationTrack.php
│   │   ├── LeaveRequest.php
│   │   ├── Client.php
│   │   ├── Device.php
│   │   ├── Notification.php
│   │   └── Setting.php
│   │
│   ├── helpers/                      # Stateless utility functions
│   │   ├── Validator.php             # Input validation
│   │   ├── Response.php              # JSON {success, data/error}
│   │   ├── Auth.php                  # JWT encode/decode + session
│   │   ├── Upload.php                # Image resize + save
│   │   ├── Logger.php                # File logging
│   │   ├── DateHelper.php            # WIB timezone, ISO 8601
│   │   └── Security.php              # AES encrypt/decrypt
│   │
│   └── middleware/                   # Request pipeline
│       ├── AuthMiddleware.php         # JWT validate OR session check
│       ├── TenantMiddleware.php       # Inject $tenantId
│       ├── CsrfMiddleware.php         # Web form CSRF
│       └── RateLimitMiddleware.php    # DB-based counter
│
├── api/v1/
│   ├── routes.php                    # Route definitions
│   └── README.md                     # API docs
│
├── cron/
│   ├── fingerspot_sync.php           # FR-11: 5-min interval
│   └── send_reminders.php            # FR-18: daily
│
├── config/
│   ├── config.php                    # App settings, constants
│   ├── database.php                  # PDO connection factory
│   └── constants.php                 # Named magic values
│
├── migrations/
│   ├── migrate.php                   # Runner: scan + execute + log
│   ├── 001_create_tenants.sql
│   ├── 002_create_employees.sql
│   ├── 003_create_devices.sql
│   ├── 004_create_attendance_sessions.sql
│   ├── 005_create_location_tracks.sql
│   ├── 006_create_leave_requests.sql
│   ├── 007_create_clients.sql
│   ├── 008_create_settings.sql
│   ├── 009_create_rate_limits.sql
│   └── 010_create_notifications.sql
│
├── logs/                             # app-YYYY-MM-DD.log (30d rotate)
└── vendor/                           # Composer: firebase/php-jwt
```

### Feature to Structure Mapping

| Feature | FRs | Controller | Model(s) | Views |
|---------|-----|-----------|----------|-------|
| Presensi Mobile | FR-1,2,3 | AttendanceController | AttendanceSession, Device | - |
| GPS Multi-mode | FR-4,5,6 | LocationController | LocationTrack, Client | clients.php |
| Lembur | FR-7,8,9,10 | OvertimeController | AttendanceSession, Tenant | overtime.php |
| Fingerspot | FR-11 | - (cron) | AttendanceSession | - |
| Dashboard HR | FR-12 | AttendanceController | AttendanceSession, Employee | home.php, attendance.php |
| Karyawan | FR-13 | EmployeeController | Employee | employees.php |
| Report | FR-14 | ReportController | AttendanceSession, Employee | report.php |
| Approval | FR-15 | ApprovalController | LeaveRequest | leave.php |
| Cuti/Izin | FR-16 | LeaveController | LeaveRequest | - |
| Notifikasi | FR-17,18,19 | NotificationController | Notification | - |
| Multi-tenant | FR-20,21 | SettingsController | Tenant, Setting | settings.php |

### Integration Boundaries

**API Boundaries:**
- `api.absenin.com` → serves Flutter mobile app (REST JSON + JWT)
- `hub.absenin.com` → serves web dashboard (PHP session + HTML)
- Both hit same `app/` layer → shared controllers, models, helpers

**Flutter Integration:**
- `services/api_client.dart` → Dio instance, base URL config, JWT interceptor
- All screens call services → services call API
- Never direct HTTP in screen code

**Fingerspot Integration (FR-11):**
- Cron script reads MySQL directly from Fingerspot machine
- Connection config in `config/fingerspot.php` (per tenant)
- Only read `att_log` table, no write

**FCM Integration (FR-18,19):**
- `NotificationController` → Firebase Admin SDK HTTP v1 API
- Server key in config
- Token storage per device in `notifications` table
