---
title: Absenin — PRD
status: draft
created: 2026-06-08
updated: 2026-06-08
---

# PRD: Absenin

*SaaS Multi-Tenant — Presensi & Workforce Tracking*

## 0. Document Purpose

PRD ini adalah kontrak fungsional untuk pengembangan Absenin v1. Dibangun di atas [Product Brief Absenin](../briefs/brief-Absenin-2026-06-08/brief.md). Target pembaca: PM/Founder (WellArtDev), downstream workflows (architecture, epics/stories, development). Semua fitur dikelompokkan dengan Functional Requirements (FR) bernomor global dan stabil. Asumsi ditandai `[ASSUMPTION]` inline dan diindeks di §9.

## 1. Vision

Absenin adalah platform SaaS multi-tenant untuk presensi dan pelacakan tenaga kerja campuran — melayani staf kantor, sales lapangan, dan teknisi on-site dalam satu sistem terpadu. Karyawan presensi via mobile app (selfie + GPS + device binding), HR memantau kehadiran real-time, lembur dihitung otomatis, dan cuti/izin dikelola dalam approval workflow terstruktur.

Dibangun untuk perusahaan Indonesia 50-500 karyawan di sektor distribusi dan jasa instalasi. v1 melakukan satu hal dengan baik: **menghilangkan rekap absensi manual dan membuat data kehadiran siap payroll.**

Monetisasi awal: penjualan putus (one-time) untuk deployment pertama. Ke depan: subscription per-bulan dengan limitasi jumlah karyawan per tier.

## 2. Target User

### 2.1 Jobs To Be Done

- **HR Manager**: "Saya butuh data kehadiran yang akurat dan siap payroll tanpa rekap manual tiap akhir bulan."
- **Sales lapangan**: "Saya butuh bukti saya benar-benar mengunjungi klien, bukan cuma GPS di jalan tol."
- **Teknisi instalasi**: "Saya butuh sistem yang adil mencatat jam kerja saya di site — termasuk lemburnya."
- **Karyawan kantor**: "Saya butuh cara absen yang simpel, bisa dari HP atau tetap pakai fingerprint."
- **Business Owner**: "Saya butuh tahu siapa yang kerja hari ini dan berapa biaya lembur bulan ini — tanpa nanya HR dulu."

### 2.2 Non-Users (v1)

- Perusahaan < 10 karyawan (overkill untuk skala mikro)
- Enterprise > 1000 karyawan (butuh fitur yang belum ada: SSO, audit trail, SLA)
- Perusahaan yang hanya punya karyawan kantor (fingerprint saja cukup)
- Industri dengan regulasi ketat (farmasi, keuangan) — belum ada compliance module

### 2.3 Key User Journeys

**UJ-1. Budi (sales) check-in di lokasi klien dan live location sampai selesai kunjungan.**

- **Persona + context:** Budi, 32, sales sound system, visit 2-3 klien per hari di kota berbeda. Selama ini laporan via WhatsApp — bosnya sering ragu dia beneran visit.
- **Entry state:** Budi sudah login. Di halaman utama, dia lihat jadwal kunjungan hari ini (opsional — `[ASSUMPTION: jadwal visit masuk v1 sebagai nice-to-have]`).
- **Path:**
  1. Budi tiba di lokasi klien, buka app → tap "Presensi"
  2. App ambil selfie + GPS + validasi device
  3. Budi pilih mode "Spesifik" → pilih klien dari daftar (preloaded dari web dashboard HR)
  4. GPS diverifikasi ≤ 10m dari koordinat klien → centang hijau
  5. Budi tap "Mulai Kunjungan" → live location aktif
- **Climax:** HR di dashboard lihat Budi "sedang di Klien X" dengan pin di map real-time.
- **Resolution:** Budi tap "Akhiri Kunjungan" saat selesai. Live location mati. Data visit tersimpan. HR bisa lihat riwayat kunjungan Budi hari ini dengan peta perjalanan.
- **Edge case:** GPS > 10m dari klien → app tampilkan peringatan "Anda berada di luar radius klien" + tombol "Laporkan Masalah" untuk situasi valid (klien pindah, GPS drift).

**UJ-2. Dian (teknisi) absen di site instalasi — auto lembur saat jam kerja habis.**

- **Persona + context:** Dian, 28, teknisi instalasi sound system. Bisa di site 8-14 jam tergantung kompleksitas proyek. Jam kerja normal 09:00-17:00. Selama ini lembur dihitung manual — sering dispute sama HR.
- **Entry state:** Dian sudah login. Di site klien, belum absen.
- **Path:**
  1. Dian tap "Presensi" di app → selfie + GPS + device validation
  2. Dian pilih mode absen "Site" → konfirmasi lokasi
  3. Clock-in pukul 08:45 → status "Hadir"
  4. Pukul 17:00 → jam kerja normal habis. Karena perusahaan setting "auto-lembur", status Dian otomatis berubah ke "Lembur"
  5. `[ASSUMPTION: notifikasi push ke Dian: "Jam kerja normal selesai. Anda sekarang dalam status Lembur."]`
- **Climax:** Dian selesai jam 21:00 → tap "Clock-out". Laporan: 8 jam normal + 4 jam lembur. Otomatis, no dispute.
- **Resolution:** HR lihat di dashboard: Dian 8 jam normal, 4 jam lembur. Data siap payroll.
- **Edge case (mode checkout→clock-in lembur):** Jika perusahaan setting "checkout dulu", pukul 17:00 Dian dapat notifikasi "Jam kerja selesai. Silakan checkout." → Dian checkout → langsung bisa clock-in lembur. Kalau Dian tidak checkout, status tetap "Hadir" tapi jam setelah 17:00 tidak dihitung (kebijakan perusahaan).

**UJ-3. Rina (HR) rekap kehadiran akhir bulan untuk payroll.**

- **Persona + context:** Rina, 35, HR manager, 65 karyawan (25 kantor, 20 sales, 20 teknisi). Sebelumnya: 3-4 hari rekap manual dari fingerprint + WhatsApp + spreadsheet. Sekarang: 1 dashboard Absenin.
- **Entry state:** Rina login ke web dashboard. Tanggal 30 Juni.
- **Path:**
  1. Dashboard menampilkan ringkasan bulan Juni: 62 hadir hari ini, 2 izin, 1 cuti
  2. Rina klik "Report" → pilih periode 1-30 Juni
  3. Sistem generate: total hari kerja, total jam normal, total jam lembur, total cuti, total izin — per karyawan
  4. Rina review: Dian (teknisi) — 22 hari kerja, 176 jam normal, 32 jam lembur. Ada 1 dispute dari Dian tanggal 15 (lembur dispute — `[ASSUMPTION: sistem punya flag dispute]`)
  5. Rina resolve dispute → approve → data Dian final
- **Climax:** Rina klik "Export" → CSV siap import ke software payroll. Selesai dalam 15 menit (sebelumnya 3-4 hari).
- **Resolution:** Rina punya confidence data akurat. Tidak ada dispute lagi setelah approve.

**UJ-4. Andi (karyawan kantor) presensi via fingerprint Fingerspot.**

- **Persona + context:** Andi, 25, staf admin. Terbiasa fingerprint, malas install app baru.
- **Path:**
  1. Andi tap jari di mesin Fingerspot pukul 08:50
  2. Konektor Absenin sinkron data Fingerspot setiap 5 menit `[ASSUMPTION: cron job sinkronisasi]`
  3. Dashboard HR update: Andi "Hadir" pukul 08:50 — sumber: Fingerspot
- **Edge case:** Fingerprint gagal → Andi bisa fallback ke mobile app untuk presensi hari itu.

**UJ-5. Rina (HR) approve pengajuan cuti dari Sari (sales).**

- **Persona + context:** Sari mengajukan cuti 2 hari via mobile app. Rina dapat notifikasi approval.
- **Path:**
  1. Rina buka dashboard → tab "Approval" → lihat pengajuan Sari: cuti 15-16 Juli, alasan: acara keluarga
  2. Rina cek kalender: tidak bentrok dengan karyawan lain yang cuti di tanggal sama `[ASSUMPTION: conflict detection sederhana di v1]`
  3. Rina tap "Setujui"
  4. Sari dapat notifikasi: "Cuti 15-16 Juli disetujui"
- **Edge case:** Rina tolak → Sari dapat notifikasi dengan alasan penolakan.

## 3. Glossary

*Semua istilah harus digunakan secara konsisten di seluruh dokumen. Sinonim tidak diizinkan.*

- **Tenant** — Satu perusahaan/kantor yang menggunakan Absenin. Data terisolasi penuh antar tenant.
- **Presensi** — Satu kali tindakan absen (clock-in, clock-out, atau visit check-in) oleh seorang karyawan.
- **Sesi Kerja** — Satu periode kerja berkelanjutan dari clock-in sampai clock-out. Dapat berisi jam normal + lembur.
- **Clock-in** — Presensi masuk/mulai bekerja.
- **Clock-out** — Presensi selesai bekerja.
- **Lembur** — Jam kerja di luar jam kerja normal yang telah dikonfigurasi tenant. Dihitung otomatis atau manual (checkout→clock-in) tergantung konfigurasi.
- **Mode GPS Spesifik** — GPS diverifikasi terhadap koordinat klien/lokasi yang sudah terdaftar. Radius toleransi: 10m.
- **Mode GPS Bebas** — GPS dicatat tanpa verifikasi terhadap koordinat tertentu.
- **Live Location** — Tracking posisi real-time selama sesi kunjungan aktif. Hanya untuk mode Spesifik dan Bebas.
- **Device Binding** — Validasi bahwa presensi dilakukan dari perangkat yang sudah terdaftar untuk karyawan tersebut.
- **Fingerspot Konektor** — Modul yang membaca data dari mesin Fingerspot (via file log, API, atau database langsung) dan menyinkronkannya ke Absenin.
- **Selfie** — Foto wajah yang diambil saat presensi. v1: disimpan sebagai bukti, tanpa face matching.
- **Dispute** — Ketidaksetujuan karyawan terhadap perhitungan lembur/kehadiran. Ditandai di sistem, perlu resolve oleh HR.

## 4. Features

### 4.1 Presensi Mobile (Selfie + GPS + Device)

**Description:** Karyawan melakukan presensi melalui mobile app (Android). Sistem merekam selfie, koordinat GPS, timestamp, dan validasi perangkat. Realizes UJ-1, UJ-2, UJ-4 (fallback).

**Functional Requirements:**

#### FR-1: Clock-in dengan selfie dan GPS

Karyawan dapat melakukan clock-in yang merekam: foto selfie, koordinat GPS (latitude, longitude, akurasi), timestamp server, dan device ID. Realizes UJ-2, UJ-4.

**Consequences (testable):**
- Selfie wajib diambil via kamera (tidak bisa upload dari galeri) — `[ASSUMPTION: Android camera intent dengan flag prevent gallery]`
- GPS harus berhasil diambil dalam 10 detik; jika gagal, presensi tetap bisa dilakukan dengan flag `gps_unverified`
- Presensi tercatat di server dalam < 3 detik setelah submit
- Response: `{ status: "success", session_id: "xxx", timestamp: "ISO8601" }`

**Out of Scope:**
- Face recognition / liveness detection (v2)
- Presensi via foto galeri

#### FR-2: Clock-out

Karyawan dapat melakukan clock-out yang mengakhiri sesi kerja aktif. Tidak memerlukan selfie. Realizes UJ-2.

**Consequences (testable):**
- Clock-out hanya bisa dilakukan jika ada sesi kerja aktif
- Sistem menghitung durasi sesi: `clock_out - clock_in`
- Jika melewati jam kerja normal, sistem menandai sesi mengandung lembur (FR-8)

#### FR-3: Validasi device

Sistem memvalidasi bahwa presensi dilakukan dari perangkat yang terdaftar untuk karyawan tersebut. Realizes UJ-1, UJ-2.

**Consequences (testable):**
- Device ID (generated saat pertama install) dikirim setiap presensi
- 1 karyawan maksimal 1 perangkat terdaftar
- Ganti perangkat → karyawan ajukan via app → HR approve → device lama di-unlink, device baru di-link
- Jika device tidak dikenal → presensi ditolak, arahkan ke pengajuan ganti perangkat

**Out of Scope:**
- Device fingerprinting hardware-level (IMEI, MAC)

### 4.2 Mode GPS untuk Sales

**Description:** Tiga mode GPS untuk sales: Spesifik (verifikasi ke klien), Bebas (tanpa verifikasi), dan Live Location (tracking real-time). Realizes UJ-1.

**Functional Requirements:**

#### FR-4: GPS mode spesifik — verifikasi ke klien

Sales memilih klien dari daftar, sistem memverifikasi GPS ≤ 10m dari koordinat klien terdaftar.

**Consequences (testable):**
- Koordinat klien disimpan di database dengan presisi 6 desimal
- Radius toleransi: 10m (configurable per tenant)
- Di luar radius → peringatan + tombol "Laporkan Masalah"
- "Laporkan Masalah" mencatat GPS aktual + alasan → masuk approval HR

#### FR-5: GPS mode bebas

Sales check-in tanpa verifikasi ke klien spesifik. GPS tetap dicatat.

**Consequences (testable):**
- Tidak ada validasi radius
- Lokasi tetap tercatat dan terlihat di dashboard HR

#### FR-6: Live location

Selama kunjungan aktif (mode Spesifik atau Bebas), sistem mengirim koordinat real-time setiap N detik.

**Consequences (testable):**
- Interval default: 60 detik (configurable per tenant: 30-300 detik)
- Live location berhenti saat karyawan "Akhiri Kunjungan"
- HR melihat pin bergerak di map dashboard
- Data lokasi disimpan sebagai track point (timestamp, lat, lng) — bisa diputar ulang (replay)

**Out of Scope:**
- Geofencing alert (karyawan keluar area)
- Estimasi jarak tempuh

### 4.3 Manajemen Lembur

**Description:** Dua mode perhitungan lembur yang bisa dipilih per tenant: auto-lembur (otomatis saat jam kerja habis) dan checkout→clock-in lembur (karyawan checkout dulu, baru clock-in lembur). Realizes UJ-2.

**Functional Requirements:**

#### FR-7: Konfigurasi aturan lembur per tenant

HR dapat memilih mode lembur: "Auto" atau "Checkout → Clock-in Lembur". Konfigurasi disimpan per tenant.

**Consequences (testable):**
- Hanya HR/admin yang bisa mengubah konfigurasi
- Perubahan tidak berlaku surut (retroaktif) — hanya untuk sesi baru

#### FR-8: Auto-lembur

Ketika jam kerja normal berakhir dan karyawan belum clock-out, status otomatis berubah ke "Lembur". Jam setelahnya dihitung sebagai lembur.

**Consequences (testable):**
- Trigger: `current_time >= work_end_time AND session.status == 'active'`
- Status berubah: `'active'` → `'overtime'`
- Durasi lembur: `clock_out_time - work_end_time`
- Karyawan dapat notifikasi: "Jam kerja normal selesai. Anda dalam status Lembur."

#### FR-9: Checkout → Clock-in Lembur

Karyawan harus checkout saat jam kerja normal selesai, lalu clock-in baru untuk lembur.

**Consequences (testable):**
- Notifikasi di jam kerja selesai: "Silakan checkout."
- Jika tidak checkout dalam 30 menit → flag `checkout_missed`, manager dapat notifikasi — `[ASSUMPTION: 30 menit grace period]`
- Clock-in lembur: presensi baru (selfie + GPS) dengan tag `lembur`
- Durasi lembur: `clock_out_lembur - clock_in_lembur`

#### FR-10: Perhitungan lembur di dashboard

Dashboard HR menampilkan total jam lembur per karyawan per periode.

**Consequences (testable):**
- Breakdown: jam normal vs jam lembur per hari
- Total akumulasi per periode (mingguan/bulanan)
- Export dalam format CSV
- Flag dispute: karyawan bisa menandai ketidaksetujuan

### 4.4 Integrasi Fingerspot

**Description:** Sinkronisasi data presensi dari mesin fingerprint Fingerspot ke Absenin. Realizes UJ-4.

**Functional Requirements:**

#### FR-11: Sinkronisasi Fingerspot

Sistem membaca data absensi dari database lokal mesin Fingerspot (MySQL, tabel `att_log`) dan menyinkronkannya ke Absenin.

**Consequences (testable):**
- Koneksi langsung ke database Fingerspot (read-only) — tidak perlu API key tambahan
- Interval sinkronisasi: setiap 5 menit (configurable) via cron
- Mapping: Fingerspot user ID → Absenin employee ID (dikonfigurasi manual oleh HR)
- Status presensi: `'fingerprint'` sebagai source
- Kegagalan sinkronisasi → log error, retry di cycle berikutnya

**Out of Scope:**
- Integrasi mesin selain Fingerspot (ZKTeco, Solution)
- Konfigurasi mesin dari dashboard Absenin (baca saja)

### 4.5 Dashboard HR (Web)

**Description:** Web dashboard untuk HR/admin: ringkasan kehadiran real-time, manajemen data karyawan, laporan payroll-ready, dan approval center. Realizes UJ-3, UJ-5.

**Functional Requirements:**

#### FR-12: Dashboard kehadiran real-time

HR melihat ringkasan kehadiran hari ini: jumlah hadir, terlambat, izin, cuti, belum absen, di lapangan.

**Consequences (testable):**
- Data real-time: refresh otomatis setiap 60 detik `[ASSUMPTION: polling interval]`
- Filter: per departemen, per lokasi, per status
- Klik karyawan → detail: jam clock-in, lokasi, selfie, sesi aktif

#### FR-13: Manajemen data karyawan

HR dapat menambah, mengedit, menonaktifkan data karyawan.

**Consequences (testable):**
- Field wajib: companyId, employeeCode, name, email, phone, ktpNumber, npwpNumber, birthDate, birthPlace, address, emergencyContactName, emergencyContactPhone, divisionId, positionId, employmentStatus, joinDate
- Field opsional: photoUrl, whatsappNumber, deviceId
- Default: leaveBalance = 12, isActive = true
- Soft delete (isActive = false) — tidak hapus permanen
- Import massal via CSV
- employeeCode auto-generated atau manual, unique per tenant

**Feature-specific NFRs:**
- ktpNumber dan npwpNumber dienkripsi (AES-256) di database

#### FR-14: Report payroll-ready

HR generate laporan kehadiran per periode yang siap digunakan untuk penggajian.

**Consequences (testable):**
- Pilih periode (tanggal mulai - tanggal selesai)
- Kolom output: nama, NIK, total hari kerja, total jam normal, total jam lembur, total cuti, total izin, total sakit
- Format export: CSV
- Subtotal per karyawan

**Out of Scope:**
- Perhitungan gaji/payroll disbursement (v2)
- BPJS, PPh 21 (v2)

#### FR-15: Approval center

HR menyetujui/menolak pengajuan cuti, izin, dan dispute lembur.

**Consequences (testable):**
- List pengajuan pending dengan filter: cuti, izin, dispute
- Detail pengajuan: karyawan, tanggal, alasan, status
- Aksi: Setujui / Tolak (dengan alasan wajib untuk tolak)
- Notifikasi ke karyawan via push/FCM

### 4.6 Pengajuan Cuti & Izin (Mobile)

**Description:** Karyawan mengajukan cuti dan izin melalui mobile app. Realizes UJ-5.

**Functional Requirements:**

#### FR-16: Pengajuan cuti/izin

Karyawan dapat mengajukan cuti atau izin dengan memilih tanggal dan alasan.

**Consequences (testable):**
- Pilih tipe: Cuti / Izin / Sakit
- Pilih tanggal (range untuk multi-hari)
- Alasan wajib diisi
- Pengajuan masuk ke approval queue HR
- Status real-time di app karyawan: pending / disetujui / ditolak

#### FR-17: Notifikasi approval

Karyawan menerima notifikasi saat pengajuan disetujui atau ditolak.

**Consequences (testable):**
- Notifikasi: Firebase Cloud Messaging (FCM)
- Fallback: status terlihat di app saat dibuka
- Jika ditolak: notifikasi menyertakan alasan penolakan

### 4.7 Notifikasi

**Description:** Push notification via FCM untuk event penting. `[ASSUMPTION: FCM gratis, cukup untuk v1]`

**Functional Requirements:**

#### FR-18: Reminder presensi

Sistem mengirim reminder presensi ke karyawan yang belum absen pada waktu yang dikonfigurasi.

**Consequences (testable):**
- Waktu reminder: configurable per tenant (default: 30 menit sebelum jam masuk)
- Hanya untuk karyawan yang belum clock-in hari itu
- Dikirim via FCM push notification

#### FR-19: Notifikasi approval

Sistem mengirim notifikasi ke karyawan saat cuti/izin disetujui atau ditolak.

**Consequences (testable):**
- Notifikasi real-time via FCM
- Fallback: status terlihat di app saat dibuka

### 4.8 Multi-Tenant

**Description:** Arsitektur multi-tenant dengan isolasi data penuh antar perusahaan. `[ASSUMPTION: isolasi via database row-level (tenant_id) — tidak separate database per tenant karena shared hosting]`

**Functional Requirements:**

#### FR-20: Isolasi data tenant

Setiap query database otomatis di-scope ke tenant_id aktif.

**Consequences (testable):**
- Setiap tabel data memiliki kolom `tenant_id`
- Middleware/auth layer menyuntikkan `tenant_id` ke setiap query
- Tidak mungkin satu tenant mengakses data tenant lain
- Tenant baru dibuat via registration + konfigurasi admin

#### FR-21: Konfigurasi per tenant

Setiap tenant dapat mengkonfigurasi: jam kerja, aturan lembur, mode GPS default, kebijakan cuti, integrasi Fingerspot.

**Consequences (testable):**
- Semua konfigurasi disimpan di tabel tenant_settings (key-value)
- Perubahan konfigurasi tidak berlaku surut

## 5. Cross-Cutting NFRs

### 5.1 Security

- **Input validation**: Semua input (form, URL params, API body, header) difilter — client-side + server-side. Server-side mandatory.
- **SQL injection**: PHP native — semua query menggunakan prepared statements (PDO). No raw SQL concatenation.
- **XSS**: Semua output di-escape (htmlspecialchars). Content-Security-Policy header.
- **CSRF**: Token CSRF di setiap form dan state-changing API request.
- **Auth**: Session-based (PHP native session) untuk web + token-based (JWT-like atau simple API key) untuk mobile API.
- **Password**: bcrypt hashing. Minimal 8 karakter.
- **Rate limiting**: API endpoint presensi: max 10 request/menit per device. Login: max 5 attempt/15 menit.

### 5.2 Performance

- **Debounce search**: Input search di-delay 300ms sebelum request ke server. `[ASSUMPTION: debounce di frontend]`
- **Search results**: Paginated, max 20 results per halaman.
- **GPS interval**: 60 detik default, configurable. Tidak lebih dari 30 detik untuk menghemat baterai.
- **Image upload**: Selfie di-resize ke max 800px sebelum upload. Format: JPEG, quality 80%.
- **Cron jobs**: Sinkronisasi Fingerspot, notifikasi reminder — dijadwalkan, tidak real-time.
- **JSON response size**: Maksimal 100KB per API response. Gunakan pagination.

### 5.3 Scalability

- **Shared hosting → VPS**: Kode harus portabel. Tanpa dependency spesifik cPanel. Gunakan relative paths.
- **Database**: MySQL/MariaDB. Gunakan indexing di kolom yang sering di-query (tenant_id, employee_id, date, status).
- **File uploads**: Selfie disimpan di filesystem, bukan database. Path: `/uploads/{tenant_id}/{employee_id}/{yyyy-mm}/`
- **Logging**: Text-based log file, rotasi per hari. Jangan spam log setiap GPS ping.
- [ASSUMPTION: queue system (gearman/redis) untuk task async seperti notifikasi — tapi mungkin tidak tersedia di shared hosting. Fallback: cron-based batch processing.]

### 5.4 Code Quality

- **No duplicate code**: Fungsi umum di-abstraksi ke helper/utility class. Contoh: `validate_input()`, `format_date()`, `api_response()`.
- **DRY**: Satu definisi untuk setiap business logic. Tidak copy-paste validasi antar file.
- **Separation of concerns**: PHP native — gunakan struktur folder: `controllers/`, `models/`, `views/`, `helpers/`, `api/`, `cron/`.
- **Naming convention**: Consistent casing — snake_case untuk PHP functions, camelCase untuk JS, UPPER_SNAKE untuk constants.
- **Max function length**: 50 baris. Max file: 500 LOC.
- [ASSUMPTION: tidak menggunakan PHP framework. Struktur manual.]

### 5.5 Maintainability

- **URL routing**: Centralized router (`index.php` + `.htaccess` rewrite).
- **Config**: Satu file config utama (`config.php`) + environment-specific override.
- **Database migrations**: File SQL sequential bernomor (`migrations/001_create_users.sql`). Tracked in version control.
- **API versioning**: URL prefix `/api/v1/`.

### 5.6 Constraints

- **Hosting**: cPanel shared hosting (v1), VPS (future). Kode harus portabel.
- **Map**: OpenStreetMap + Leaflet.js. Gratis, no API key. `[ASSUMPTION: Leaflet cukup untuk display dan basic interaction]`
- **Push notification**: Firebase Cloud Messaging (FCM). Gratis, unlimited.
- **No Docker, no Node.js runtime, no Redis**: Semua harus jalan di LAMP stack standar.

## 6. Non-Goals (Explicit)

- **Bukan HRIS lengkap** — tidak ada payroll disbursement, BPJS, PPh 21
- **Bukan aplikasi komunikasi** — tidak ada chat internal atau feed
- **Bukan project management** — tidak ada task tracking, timesheet, atau rencana kerja
- **Bukan attendance hardware** — tidak menjual mesin fingerprint, hanya integrasi
- **Tidak menggantikan software payroll yang sudah ada** — Absenin menyediakan data, bukan eksekusi gaji

## 7. MVP Scope

### 7.1 In Scope

- Multi-tenant dengan isolasi data
- Mobile app Android (APK)
- Web dashboard HR
- Presensi: selfie + GPS + device validation
- 3 mode GPS (Spesifik, Bebas, Live Location)
- 2 mode lembur (Auto, Checkout→Clock-in)
- Integrasi Fingerspot (baca)
- Manajemen data karyawan (CRUD + import CSV)
- Report payroll-ready (CSV export)
- Approval cuti/izin/lembur dispute
- Pengajuan cuti/izin dari mobile
- Push notification (FCM)
- Notifikasi reminder presensi
- Input validation + sanitization
- Debounce search
- Prepared statements (PDO)
- OpenStreetMap + Leaflet
- PHP native, MySQL, cPanel shared hosting

### 7.2 Out of Scope for MVP

- Face recognition / liveness detection → v2
- Integrasi payroll disbursement (BPJS, PPh 21) → v2
- Shift scheduling → v2
- Timesheet → v2
- iOS app → v2
- Multi-bahasa (English) → v2
- Integrasi mesin fingerprint selain Fingerspot → v2
- Subscription billing system → sebelum launch komersial
- Admin panel super-admin untuk manage tenants → v1: database langsung
- Dark mode → nice-to-have

## 8. Success Metrics

**Primary**
- **SM-1**: Waktu rekap absensi HR ≤ 15 menit per bulan (sebelumnya 3-4 hari). Validates FR-14.
- **SM-2**: Akurasi perhitungan lembur 100% — 0 dispute yang terbukti kesalahan sistem. Validates FR-8, FR-9, FR-10.
- **SM-3**: Onboarding tenant baru (registrasi → presensi pertama) ≤ 4 jam. Validates FR-13, FR-21.

**Secondary**
- **SM-4**: GPS accuracy ≤ 10m dalam 90% presensi mode Spesifik. Validates FR-4.
- **SM-5**: Selfie upload success rate ≥ 98%. Validates FR-1.
- **SM-6**: Fingerspot sync success rate ≥ 99% (retry resolved). Validates FR-11.

**Counter-metrics (do not optimize)**
- **SM-C1**: Jumlah notifikasi per karyawan per hari ≤ 5 — jangan spam. Counterbalances FR-18.
- **SM-C2**: GPS ping tidak menyebabkan baterai drop > 10% selama 8 jam kerja. Counterbalances FR-6.

## 9. Monetization

- **v1**: Penjualan putus (one-time fee) untuk deployment pertama — kantor teman. Mencakup: setup tenant, konfigurasi, training dasar.
- **v2+**: Subscription per-bulan dengan tier limitasi jumlah karyawan. Contoh:
  - Starter: max 20 karyawan
  - Professional: max 100 karyawan
  - Enterprise: unlimited
- `[ASSUMPTION: sistem tidak memiliki billing module di v1. Pembayaran manual/offline.]`

## 10. Platform & Tech Stack

- **Backend**: PHP 8.x native (no framework), MySQL/MariaDB
- **Frontend (Web Dashboard)**: HTML5, CSS3, JavaScript vanilla
- **Mobile**: Flutter — dipilih karena cross-platform readiness (Android v1, iOS ready), security maturity, dan performa native
- **Map**: OpenStreetMap + Leaflet.js (web), flutter_map + OpenStreetMap (mobile)
- **Push**: Firebase Cloud Messaging
- **Auth mobile**: JWT dengan refresh token rotation
- **Hosting**: cPanel shared hosting (Apache, PHP, MySQL)
- **Version control**: Git

## 11. Open Questions

1. ~~Flutter vs native Android untuk mobile app?~~ → **Flutter** — cross-platform, secure, performa native.
2. ~~Preload daftar klien dari dashboard?~~ → **v1**.
3. ~~Conflict detection cuti — sederhana atau detail?~~ → **Detail**: sistem deteksi bentrok tanggal + jabatan/departemen yang sama. Notifikasi ke HR saat ada potensi konflik sebelum approve.
4. ~~Fingerspot: akses via file log, API, atau DB langsung?~~ → **Database langsung** — baca tabel `att_log` di MySQL lokal mesin Fingerspot. Paling umum di Indonesia, tidak perlu API/key tambahan.
5. ~~Dispute resolution — perlu escalation ke atas?~~ → **Final di HR**. HR decision final. Tidak ada banding ke level manager/director di v1. Log alasan penolakan untuk audit.
6. ~~Auth mobile: JWT atau API key?~~ → **JWT** dengan refresh token rotation.

## 12. Assumptions Index

*Setiap `[ASSUMPTION]` dari dokumen, disurface untuk konfirmasi eksplisit:*

- ~~Jadwal visit sales masuk v1 sebagai nice-to-have~~ → diputuskan: preload daftar klien dari dashboard (v1)
- ~~Daftar klien bisa di-preload dari web dashboard~~ → dikonfirmasi: v1, preloaded dari HR dashboard
- ~~Maksimal 2 device per karyawan~~ → dikonfirmasi: maksimal 1 device, ganti device butuh approval HR
- Conflict detection cuti detail — deteksi bentrok tanggal + jabatan/departemen, notifikasi ke HR
- Fingerspot: akses database langsung (MySQL, tabel att_log), read-only
- Dispute resolution: final di HR, tidak ada banding ke atas
- Auth: JWT dengan refresh token rotation
- Flutter untuk mobile app (cross-platform, secure, performa native)
- Android camera intent — prevent gallery upload (butuh kamera langsung)
- Notifikasi push "Anda dalam status Lembur" — §2.3 UJ-2
- Sistem punya flag dispute — §2.3 UJ-3
- Cron job sinkronisasi Fingerspot — §2.3 UJ-4
- Grace period checkout 30 menit — §4.3 FR-9
- Dashboard polling interval 60 detik — §4.5 FR-12
- Isolasi tenant via row-level (tenant_id), bukan separate DB — §4.8
- Debounce search 300ms di frontend — §5.2
- Queue system (Gearman/Redis) mungkin tidak tersedia di shared hosting — fallback: cron-based batch — §5.3
- Tidak menggunakan PHP framework — struktur manual — §5.4
- FCM gratis, cukup untuk v1 — §4.7
- Leaflet.js + flutter_map cukup untuk map display — §5.6
- No billing module di v1 — §9
