# Absenin

SaaS Multi-Tenant — Presensi & Workforce Tracking.

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend API | PHP 8.x native + MySQL |
| Frontend Dashboard | Next.js 14 + Tailwind CSS |
| Mobile | Flutter |

## Project Structure

```
backend/            # PHP REST API
├── public/         # Web root (API front controller)
├── app/            # Controllers, Models, Helpers, Middleware
├── api/v1/         # Route definitions
├── config/         # App + DB configuration
├── migrations/     # SQL migration files
├── cron/           # Scheduled jobs
└── logs/           # App logs (30d rotation)

frontend/           # Next.js Dashboard
├── app/            # Next.js App Router pages
├── components/     # Shared UI components
├── lib/            # API client + utilities
└── public/         # Static assets
```

## Local Dev

### Backend (Laragon)
```bash
cd backend
cp .env.example .env
composer install
# Set virtual host: api.absenin.test → backend/public/
php migrations/migrate.php
```

### Frontend (Next.js)
```bash
cd frontend
cp .env.local.example .env.local
npm install
npm run dev
```

## API

Base URL: `http://api.absenin.test/api/v1`

| Method   | Endpoint           | Description      |
|----------|--------------------|------------------|
| GET      | `/`                | Health check     |
| POST     | `/auth/login`      | Login            |
| POST     | `/auth/refresh`    | Refresh JWT      |

## Deployment

### Backend: cPanel shared hosting
- Git deploy ke `/home/absenin/api.absenin.com/`
- Document root: `backend/public/`
- Run migrations: `GET /migrate.php?key=MIGRATE_KEY`

### Frontend: Vercel / VPS
- `NEXT_PUBLIC_API_URL=https://api.absenin.com`
- API proxy via `next.config.js` rewrites
