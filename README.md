# Absenin

SaaS Multi-Tenant — Presensi & Workforce Tracking.

## Tech Stack
- PHP 8.x native
- MySQL/MariaDB
- Flutter (mobile)
- Vanilla HTML/CSS/JS (web dashboard)

## Local Dev (Laragon)
1. Place project in `C:\laragon\www\absenin`
2. Set virtual host: `api.absenin.test` → `public/`
3. Run `composer install`
4. Copy `.env.example` to `.env`, configure database
5. Run `php migrations/migrate.php`

## API
Base URL: `http://api.absenin.test/api/v1`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Health check |
| POST | `/auth/login` | Login (email + password) |
| POST | `/auth/refresh` | Refresh JWT token |

## Project Structure
```
public/    → Web root (API + Dashboard)
app/       → Controllers, Models, Helpers, Middleware
api/v1/    → Route definitions
config/    → App + DB configuration
migrations/→ SQL migration files
cron/      → Scheduled jobs
logs/      → App logs (30d rotation)
```
