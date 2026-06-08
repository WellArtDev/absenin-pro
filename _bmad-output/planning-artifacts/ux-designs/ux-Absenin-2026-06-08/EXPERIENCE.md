---
title: Absenin — EXPERIENCE.md
status: draft
created: 2026-06-08
updated: 2026-06-08
---

# EXPERIENCE.md — Absenin

*Information architecture, behavior, states, interactions, accessibility, key flows.*

## Foundation

- **Form-factor**: Mobile (Flutter — karyawan) + Web dashboard (vanilla HTML/CSS — HR/admin)
- **Visual identity**: [`DESIGN.md`](./DESIGN.md) — semua token visual (colors, typography, spacing, rounded, elevation) merujuk ke DESIGN.md menggunakan notasi `{colors.primary.600}`.
- **UI system**: CSS custom properties (web) + Flutter ThemeData (mobile). No third-party component library — lightweight, sesuai constraint shared hosting + PHP native.
- **Navigation model**: Mobile: bottom tab (3-5 tab). Web: sidebar persistent + top breadcrumb.
- **Auth surface**: Login page standalone. No auth = tidak bisa akses apapun.

## Information Architecture

### Mobile App (Karyawan)

```
App Root
├── Login / Onboarding
├── Home (Dashboard)
│   ├── Status hari ini (clocked in/out, status lembur)
│   ├── Quick action: Presensi (FAB)
│   └── Ringkasan mingguan
├── Presensi
│   ├── Camera selfie (fullscreen)
│   ├── GPS capture (auto)
│   ├── Mode pilih (Spesifik / Bebas — sales)
│   ├── Konfirmasi (preview selfie + lokasi + waktu)
│   └── Success / Error screen
├── Riwayat
│   ├── Daftar sesi kerja (list, scrollable)
│   ├── Detail sesi (selfie, lokasi, durasi, status)
│   └── Filter: hari ini / minggu ini / bulan ini
├── Pengajuan
│   ├── Form cuti (tipe, tanggal, alasan)
│   ├── Form izin (tipe, tanggal, alasan)
│   ├── Status pengajuan (pending/disetujui/ditolak)
│   └── Detail pengajuan
└── Profil
    ├── Info karyawan (read-only)
    ├── Pengaturan device (device ID, ganti device)
    ├── Logout
    └── Tentang app
```

### Web Dashboard (HR/Admin)

```
Dashboard Root
├── Login
├── Dashboard Home
│   ├── Stats cards: hadir / terlambat / izin / cuti / belum absen
│   ├── Real-time list: siapa hadir hari ini
│   ├── Map view: live location karyawan lapangan
│   └── Quick links: approval pending, report
├── Karyawan
│   ├── List karyawan (table, search, filter divisi/status)
│   ├── Detail karyawan (profil lengkap, riwayat absensi)
│   ├── Tambah / Edit karyawan (form)
│   └── Import CSV
├── Presensi
│   ├── Log presensi harian (table, filter tanggal/departemen)
│   ├── Detail presensi (selfie, lokasi di map, device)
│   └── Export CSV
├── Lembur
│   ├── Daftar sesi lembur (table, filter)
│   ├── Dispute management (list, resolve)
│   └── Ringkasan lembur per periode
├── Cuti & Izin
│   ├── Approval queue (pending list)
│   ├── Riwayat cuti/izin (per karyawan)
│   └── Conflict detection (bentrok otomatis)
├── Report
│   ├── Generate report (pilih periode, pilih karyawan/departemen)
│   ├── Preview report (table summary)
│   └── Export CSV
├── Klien & Lokasi
│   ├── Daftar klien (CRUD)
│   ├── Koordinat per klien (pilih via map)
│   └── Radius toleransi (default 10m)
├── Pengaturan
│   ├── Jam kerja (start, end, toleransi terlambat)
│   ├── Mode lembur (Auto / Checkout→Clock-in)
│   ├── Kebijakan cuti (maks hari, aturan)
│   ├── Integrasi Fingerspot (konfigurasi koneksi)
│   └── Notifikasi (reminder time)
└── Profil Admin
    ├── Info akun
    └── Logout
```

## Voice and Tone

**Microcopy guidelines**:

- **Bahasa**: Indonesian. Formal sopan, tapi tidak kaku. Hindari istilah teknis (karyawan tidak perlu tahu "API" atau "sync").
- **Button**: Kata kerja imperatif spesifik. "Clock In" (bukan "Submit"), "Ajukan Cuti" (bukan "Kirim"), "Setujui" (bukan "OK").
- **Error**: Jangan blaming. "GPS tidak dapat diambil. Pastikan lokasi diaktifkan." (bukan "GPS error").
- **Success**: Konfirmasi + next step. "Presensi berhasil. Anda tercatat Hadir pukul 08:50."
- **Empty state**: Friendly + actionable. "Belum ada pengajuan cuti. Ajukan sekarang?" dengan button.
- **Loading**: Konteks. "Mengambil data kehadiran..." bukan "Loading..."

**Tone spectrum**: HR dashboard → slightly more formal. Mobile app → lebih santai tapi tetap profesional. Notifikasi → singkat, actionable.

## Component Patterns

### Presensi Flow (Mobile)

- **FAB**: Tombol hijau bulat di kanan bawah, position fixed, elevation `{elevation.lg}`. Icon: camera (Phosphor `Camera` fill). Selalu visible di Home + Riwayat tab. GONE saat di halaman pengajuan/profil.
- **Camera screen**: Fullscreen. Viewfinder center dengan guide oval. Tombol capture di bawah. Tidak ada gallery picker. Flash otomatis.
- **GPS indicator**: Di atas tombol capture — status dot + text. Hijau: "Lokasi akurat (3m)". Kuning: "Mencari lokasi...". Merah: "Lokasi tidak tersedia".
- **Selfie preview**: Setelah capture: foto di tengah, koordinat + timestamp di bawah, tombol "Kirim" + "Ulangi". Bukan fullscreen — biar konteks tetap terlihat.
- **Success**: Centang hijau animasi ringan + teks konfirmasi + jam + status. Auto-dismiss setelah 3 detik ke Home.

### Live Location (Mobile)

- **Indicator**: Saat live location aktif: banner persisten di atas app bar — "Live Location aktif · Klien X" dengan dot hijau berkedip.
- **Stop**: Tombol "Akhiri Kunjungan" di Home screen, warna merah soft (danger/ghost), konfirmasi dialog sebelum stop.

### Dashboard Real-Time (Web)

- **Auto-refresh**: Data kehadiran refresh setiap 60 detik. Indicator di pojok kanan atas: "🔄 30 detik lalu" dengan tombol manual refresh.
- **Status filter tabs**: "Semua · Hadir · Terlambat · Izin · Cuti" — horizontal tabs.
- **Employee row**: Avatar (foto/initial) + nama + jabatan + status badge + jam clock-in. Klik → expand detail.

### Map View (Web)

- **Library**: Leaflet.js + OpenStreetMap tiles.
- **Marker**: Pin hijau per karyawan aktif di lapangan. Klik marker → popup: nama, lokasi, jam mulai, durasi.
- **Replay mode**: Untuk riwayat kunjungan sales — tombol "Putar" dengan timeline scrub. Track line biru. Marker bergerak sesuai track point.

### Approval Queue (Web)

- **List item**: Foto + nama + tipe (Cuti/Izin/Dispute) + tanggal + alasan singkat + 2 tombol: "Setujui" (green) + "Tolak" (red outline).
- **Tolak flow**: Klik tolak → modal dengan textarea wajib "Alasan penolakan" → konfirmasi.
- **Conflict badge**: Jika pengajuan bentrok dengan karyawan lain → badge kuning "⚠️ Bentrok dengan [nama]" di item.

### Search & Filter

- **Debounce**: Input search delay 300ms sebelum request. Tidak ada submit button — auto-search.
- **Web**: Search bar di atas table. Filter chips: divisi, status, tanggal.
- **Mobile**: Search bar sticky di atas list. Filter via bottom sheet.

## State Patterns

### Loading
- **Initial**: Skeleton screen — placeholder abu-abu dengan pulse animation. Bukan spinner kosong.
- **Action**: Button disabled + spinner kecil di dalam button. Bukan fullscreen overlay.
- **List**: Infinite scroll dengan loader di bawah (bukan "Load More" button).

### Empty
- **No data**: Ilustrasi ringan (icon besar, warna neutral 200) + teks + CTA. Contoh: "Belum ada presensi hari ini" di halaman riwayat.
- **No results**: "Tidak ada hasil untuk '...'. Coba kata kunci lain." — muncul setelah search.
- **No approval pending**: "Semua pengajuan sudah diproses. 🎉"

### Error
- **Network**: Toast di bawah — "Koneksi terputus. Mencoba lagi..." dengan auto-retry.
- **GPS gagal**: Inline warning — bukan blocking. Presensi tetap bisa dengan flag `gps_unverified`.
- **Server error**: Toast error + "Coba lagi" button. Jangan tampilkan stack trace atau kode error ke user.
- **Validation**: Inline per field — merah, teks kecil di bawah input. Bukan alert dialog.

### Success
- **Toast**: Animasi slide dari atas (mobile) / pojok kanan atas (web). Auto-dismiss 3 detik.
- **Presensi success**: Fullscreen sebentar (1.5 detik) — centang besar + status + jam. Lalu ke Home.

## Interaction Primitives

- **Tap/Click**: Primary action trigger. Respons < 100ms (visual feedback: opacity/scale).
- **Long press**: Web: tidak ada. Mobile: pada item list → context menu (opsional untuk delete/edit di v2).
- **Swipe**: Tidak ada di v1. Navigasi antar tab via tap.
- **Pull to refresh**: Di list (Riwayat, Karyawan). Indicator Material Design.
- **Haptic**: Mobile: light haptic pada clock-in success. Web: tidak ada.
- **Back navigation**: Mobile: gesture back (default) atau tombol back di app bar. Web: breadcrumb + browser back.
- **Modal**: Overlay semi-transparan. Tap di luar = close. Scrollable jika konten panjang.

## Accessibility Floor

- **Contrast**: Semua teks memenuhi WCAG 2.1 AA (ratio ≥ 4.5:1 untuk body, ≥ 3:1 untuk large text). Hijau #059669 di atas putih = 4.6:1 (pass).
- **Touch target**: Minimal 44×44px untuk semua elemen interaktif (mobile).
- **Label**: Semua input punya label. Tidak placeholder-only.
- **Error**: Tidak warna-saja. Error state selalu dengan ikon + teks.
- **Focus**: Visible focus ring (2px solid `{colors.border.focus}`) pada semua elemen interaktif di web.
- **Screen reader**: Semantic HTML (web). Flutter: Semantics widget untuk key elements.
- **Language**: `lang="id"` di HTML root. Flutter: `Localizations` default ID.
- **Motion**: Respect `prefers-reduced-motion`. Animasi ≤ 200ms. Bisa dimatikan.

## Key Flows

### Flow 1: Budi (sales) — Kunjungan Klien (UJ-1)

1. **Home**: Budi buka app. Status: "Belum presensi". FAB kamera di kanan bawah.
2. **Presensi screen**: Kamera aktif. GPS indicator hijau: "Lokasi akurat (5m)". Budi selfie.
3. **Mode selection**: Pilih mode "Spesifik". List klien muncul — Budi pilih "SoundPro Jakarta".
4. **Konfirmasi**: Preview selfie + lokasi di map kecil + "SoundPro Jakarta (dalam radius)". Budi tap "Mulai Kunjungan".
5. **Success**: Centang hijau. "Kunjungan dimulai · SoundPro Jakarta · 09:15". Auto-dismiss ke Home.
6. **Home (selama visit)**: Banner "Live Location aktif · SoundPro Jakarta". Map kecil di bawah.
7. **Akhiri kunjungan**: Budi tap "Akhiri Kunjungan" → konfirmasi dialog → selesai. Banner hilang.

**Climax beat**: Di dashboard HR, pin Budi muncul di map dengan label "SoundPro Jakarta" — HR bisa lihat dia beneran di lokasi klien.

### Flow 2: Dian (teknisi) — Lembur Otomatis (UJ-2)

1. **Home**: Dian buka app. FAB → kamera → selfie → clock-in: 08:45. Status: "Hadir · 08:45".
2. **Sepanjang hari**: Status tetap "Hadir". Waktu berjalan real-time.
3. **17:00**: Notifikasi push: "Jam kerja normal selesai. Anda dalam status Lembur." Status otomatis berubah ke "Lembur · sejak 17:00". Timer lembur berjalan.
4. **21:00**: Dian selesai. Tap clock-out. Ringkasan: 8 jam normal + 4 jam lembur.
5. **Success**: "Clock-out berhasil. Total: 12 jam (4 jam lembur)."

**Climax beat**: Di dashboard HR → Dian: 8 jam normal + 4 jam lembur. Otomatis. No dispute.

### Flow 3: Rina (HR) — Rekap Bulanan (UJ-3)

1. **Dashboard Home**: Rina login ke web. Ringkasan hari ini: 62 hadir, 2 izin, 1 cuti. Ada 3 dispute pending.
2. **Report**: Rina klik "Report" di sidebar. Pilih periode 1-30 Juni. Pilih semua departemen.
3. **Preview**: Tabel: nama, NIK, hari kerja, jam normal, jam lembur, cuti, izin. Total di bawah.
4. **Resolve dispute**: Ada flag dispute di kolom Dian (15 Juni: 2 jam lembur dispute). Rina klik → detail → check lokasi + jam di map → "Setujui". Dispute resolved, data Dian update.
5. **Export**: Semua data clean. Rina klik "Export CSV". File terdownload.

**Climax beat**: 15 menit. Sebelumnya 3-4 hari.

### Flow 4: Andi (kantor) — Fingerprint (UJ-4)

1. **Fingerspot**: Andi tap jari di mesin 08:50.
2. **Sync**: 5 menit kemudian, cron job sync data. Dashboard update: Andi "Hadir · 08:50 · Fingerprint".
3. **Fallback**: Fingerprint gagal → Andi buka app → presensi mobile → clock-in. Tetap tercatat.

**Climax beat**: Andi ga perlu install app. HR tetap lihat datanya di dashboard yang sama.

### Flow 5: Sari (sales) → Rina (HR) — Approval Cuti (UJ-5)

1. **Mobile**: Sari di app → tab Pengajuan → "Ajukan Cuti". Pilih tipe: Cuti Tahunan. Tanggal: 15-16 Juli. Alasan: "Acara keluarga". Submit.
2. **Feedback**: Toast: "Pengajuan cuti dikirim. Menunggu approval."
3. **Web**: Dashboard Rina muncul badge "3" di menu Cuti & Izin (pending count). Juga notifikasi browser.
4. **Approval queue**: Rina lihat pengajuan Sari. Ada badge kuning: "⚠️ Bentrok dengan Budi (15 Juli)". Tapi Rina decide tetap approve — alasannya beda departemen.
5. **Approve**: Rina klik "Setujui". Badge hilang.
6. **Mobile**: Sari dapat notifikasi push: "Cuti 15-16 Juli disetujui." Di app, status berubah jadi "Disetujui".

**Climax beat**: End-to-end dalam < 1 jam dari pengajuan ke approval. No WhatsApp, no kertas.

---

## Responsive & Platform

### Mobile (Flutter)
- **Min SDK**: Android 7.0 (API 24)
- **Orientation**: Portrait only (v1). Landscape untuk map view opsional.
- **Gesture**: Back gesture default Android. Pull-to-refresh.
- **Keyboard**: Tidak menutupi input (resize view). Camera: fullscreen, rotasi otomatis.
- **Battery**: GPS interval 60 detik. Wakelock hanya saat live location aktif.

### Web Dashboard
- **Min width**: 1024px. Tidak responsive ke mobile (HR pakai desktop/laptop).
- **Tablet**: Not targeted in v1. Layout statis di 1024-1440px optimal.
- **Browser**: Chrome, Firefox, Edge. No IE11.
- **Print**: Report page punya print stylesheet (hide sidebar, full-width table).
