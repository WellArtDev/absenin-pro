# Absenin Frontend

Next.js dashboard untuk Absenin — SaaS Multi-Tenant Presensi & Workforce Tracking.

## Setup

```bash
cd frontend
npm install
cp .env.local.example .env.local
# Edit .env.local: NEXT_PUBLIC_API_URL=http://api.absenin.test
npm run dev
```

## Struktur

```
frontend/
├── app/                  # Next.js App Router pages
│   ├── layout.tsx        # Root layout (Inter font, metadata)
│   ├── page.tsx          # Landing page
│   └── globals.css       # Tailwind + design tokens
├── components/           # Shared UI components
├── lib/
│   └── api.ts            # API client (calls backend)
├── public/               # Static assets
├── next.config.js        # API proxy config
├── tailwind.config.js    # Theme (primary color: #059669)
└── package.json
```

## API Proxy

Semua request ke `/api/*` di-proxy ke `NEXT_PUBLIC_API_URL` via `next.config.js` rewrites. JWT auth via httpOnly cookie (`credentials: 'include'`).

## Pages (TODO)

- `/` — Landing
- `/login` — Login page
- `/dashboard` — Dashboard home (real-time stats)
- `/dashboard/employees` — Manajemen karyawan
- `/dashboard/attendance` — Log presensi
- `/dashboard/leaves` — Cuti & izin approval
- `/dashboard/reports` — Report payroll
- `/dashboard/settings` — Pengaturan tenant
