# Deploy Next.js to cPanel (Node.js)

## 1. Build local
```bash
cd frontend
npm run build
```

## 2. Upload ke cPanel

**⚠️ CloudLinux NodeJS Selector**: node_modules harus di virtual environment terpisah — jangan upload `node_modules/` ke app root! nanti cPanel bikin symlink otomatis.

Upload file + folder berikut:
- `.next/`
- `public/`
- `package.json`
- `next.config.ts`

Target folder: `/home/absenin/hub.absenin.com/`

## 3. Setup Node.js di cPanel
1. Buka cPanel → **Setup Node.js App**
2. Create Application:
   - **Node.js version**: 22.x (latest stable)
   - **Application mode**: Production
   - **Application root**: `/home/absenin/hub.absenin.com`
   - **Application URL**: `hub.absenin.com`
    - **Application startup file**: `node_modules/.bin/next`
    - **Passenger startup file**: (kosongkan)
3. **Environment Variables**:
   - **Passenger startup file**: (kosongkan, pake node_modules/.bin/next)
3. Environment Variables:
   ```
   NODE_ENV=production
   NEXT_PUBLIC_API_URL=https://api.absenin.com
   PORT=3000
   ```
4. Klik **Create** / **Save**

## 4. Install dependencies
Setelah app terdaftar, cPanel akan muncul tombol **"Run NPM Install"**.
Klik itu untuk install dependencies (akan buat `node_modules/`).

## 5. Start / Restart
Klik **Restart** untuk menjalankan app.
Cek logs kalau ada error.

## 6. Domain
Setup subdomain `hub.absenin.com` → document root kosong (cPanel Node.js handle sendiri via Passenger).
