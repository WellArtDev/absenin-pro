# Deploy Next.js to cPanel (CloudLinux Node.js)

## 1. Upload source (via FTP / Git deploy)

Upload SEMUA file kecuali `node_modules/`:

```
app/            # App Router pages
components/     # Shared components
lib/            # API client
public/         # Static assets
package.json
next.config.ts
tsconfig.json
postcss.config.mjs
```

Target: `/home/absenin/hub.absenin.com/`

## 2. Delete node_modules

Kalau ada folder `node_modules` di app root — **HAPUS**. CloudLinux bikin symlink sendiri.

## 3. Setup Node.js di cPanel
1. Buka cPanel → **Setup Node.js App**
2. Create Application:
   - **Node.js version**: 22.x
   - **Application mode**: Production
   - **Application root**: `/home/absenin/hub.absenin.com`
   - **Application URL**: `hub.absenin.com`
   - **Application startup file**: `node_modules/.bin/next`
3. Environment Variables:
   ```
   NODE_ENV=production
   NEXT_PUBLIC_API_URL=https://api.absenin.com
   ```
4. Klik **Create**

## 4. Install + Build
1. Klik **"Run NPM Install"** — install dependencies
2. Buka **Terminal** (atau SSH kalau ada) dan jalankan:
   ```bash
   cd /home/absenin/hub.absenin.com
   npm run build
   ```
   Atau tambahkan `"build"` ke startup script dengan bikin file `.cloudlinux.sh`:

   ```bash
   #!/bin/bash
   npm run build && next start
   ```
   Lalu ganti startup file ke `.cloudlinux.sh`

## 5. Start
Klik **Restart**. Cek logs kalau ada error.
