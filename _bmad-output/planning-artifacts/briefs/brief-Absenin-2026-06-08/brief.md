---
title: "Product Brief: Absenin"
status: draft
created: 2026-06-08
updated: 2026-06-08
---

# Product Brief: Absenin

## Executive Summary

Absenin adalah platform SaaS multi-tenant untuk manajemen kehadiran dan pelacakan tenaga kerja. Dibangun untuk perusahaan dengan tenaga kerja campuran — staf kantor, sales lapangan, dan teknisi on-site — yang tidak bisa mengandalkan mesin fingerprint saja.

Platform ini menggabungkan presensi mobile (selfie + GPS + validasi perangkat), integrasi mesin fingerprint, pelacakan lokasi real-time untuk tim lapangan, perhitungan lembur otomatis, dan dashboard HR yang menyatukan data kehadiran, lembur, serta persetujuan cuti/izin dalam satu tempat.

Sasaran awal: perusahaan menengah (50+ karyawan) di sektor distribusi dan jasa instalasi, dengan arsitektur multi-tenant yang siap melayani berbagai industri.

## The Problem

Perusahaan dengan tenaga kerja campuran menghadapi masalah kehadiran yang tidak bisa diselesaikan oleh mesin fingerprint:

- **Sales di luar kota** tidak bisa absen dari kantor. GPS generic tidak cukup — perlu verifikasi bahwa mereka benar-benar mengunjungi klien yang dituju.
- **Teknisi instalasi di site** bekerja dengan jam tidak menentu. HR kesulitan menghitung jam kerja normal vs lembur secara manual — rawan salah hitung dan dispute.
- **HR kewalahan** dengan rekapitulasi manual: mengumpulkan data dari fingerprint, spreadsheet sales, laporan teknisi, lalu mencocokkan lembur, cuti, dan izin untuk payroll setiap bulan.
- **Titip absen** masih terjadi. Selfie biasa bisa dipalsukan dengan foto. Perlu minimal device binding + GPS + foto sebagai lapisan verifikasi.

Status quo: kombinasi fingerprint (kantor) + WhatsApp/telepon (lapangan) + Excel (HR). Lambat, rawan kecurangan, dan tidak scalable.

## The Solution

Absenin memberikan satu platform terpadu:

### Untuk Karyawan (Mobile App)
- **Presensi selfie** dengan validasi GPS dan device binding — mencegah titip absen
- **3 mode GPS untuk sales**: lokasi klien spesifik (verified), mode bebas, dan live location tracking
- **Teknisi lapangan**: clock-in di site, dan opsi lembur yang dikonfigurasi HR — auto-lembur saat jam kerja habis, atau checkout dulu lalu clock-in lembur
- **Karyawan kantor**: presensi via mobile app atau mesin fingerprint Fingerspot (tersinkronisasi)
- **Pengajuan cuti & izin** langsung dari app, dengan tracking status approval

### Untuk HR (Web Dashboard)
- **Dashboard real-time**: siapa hadir, terlambat, belum absen, atau sedang di lapangan
- **Manajemen data karyawan**: profil, jabatan, jadwal kerja, lokasi penugasan
- **Perhitungan lembur otomatis**: berdasarkan jam kerja + aturan yang dikonfigurasi per perusahaan
- **Report payroll-ready**: ringkasan kehadiran, lembur, cuti, izin — siap digunakan untuk penggajian
- **Approval center**: menyetujui/menolak cuti, izin, dan klaim lembur

> **Multi-tenant**: Setiap perusahaan memiliki environment terisolasi dengan konfigurasi sendiri — jam kerja, aturan lembur, mode GPS, kebijakan cuti, dan integrasi fingerprint.

## What Makes This Different

- **Dibangun untuk tenaga kerja campuran dari awal** — bukan absensi kantor yang "ditambahi" fitur lapangan, tapi dirancang untuk melayani office, sales, dan field technician secara setara
- **3 mode GPS untuk sales** — spesifik (per klien), bebas, dan live location — lebih dari sekadar radius check-in
- **Fleksibilitas aturan lembur** — HR bisa memilih auto-lembur atau checkout→clock-in, disesuaikan dengan kebijakan perusahaan
- **Integrasi Fingerspot native** — perusahaan yang sudah invest di fingerprint tidak perlu buang mesin
- **Fokus Indonesia**: bahasa, zona waktu, aturan ketenagakerjaan, dan integrasi payroll lokal

## Who This Serves

### Primary: HR Manager / Admin
- Mengelola 50-200+ karyawan
- Saat ini rekap absensi manual (fingerprint + Excel + WhatsApp)
- Butuh: data akurat untuk payroll, proses approval terstruktur, visibilitas real-time

### Primary: Karyawan Lapangan (Sales & Teknisi)
- Sales: butuh bukti kunjungan klien yang diverifikasi GPS — bukan cuma "di mana aja"
- Teknisi: jam kerja tidak standar, butuh sistem yang adil menghitung lembur mereka
- Keduanya: butuh app yang ringan dan tidak menguras baterai

### Secondary: Karyawan Kantor
- Transisi mulus dari fingerprint ke mobile (atau tetap fingerprint)
- Pengajuan cuti/izin tanpa kertas

### Secondary: Business Owner / Director
- Visibilitas: siapa kerja, siapa tidak, berapa biaya lembur bulan ini
- Data untuk keputusan: understaffed? overtime terlalu tinggi?

## Success Criteria

| Metric | Target |
|--------|--------|
| Waktu rekap absensi HR | 90% lebih cepat vs manual |
| Akurasi perhitungan lembur | 100% (otomatis, no manual calc) |
| Onboarding tenant baru | < 1 hari (self-service signup + konfigurasi) |
| GPS accuracy | ≤ 10 meter untuk mode spesifik |
| Selfie verification rate | ≥ 95% berhasil di first attempt |
| Churn rate | < 5% per bulan |

## Scope

### In (V1)
- Multi-tenant SaaS architecture
- Mobile app (Android minimum, iOS stretch)
- Web dashboard untuk HR
- Presensi: selfie + GPS + device validation
- 3 mode GPS (spesifik, bebas, live location)
- Aturan lembur: auto-lembur dan checkout→clock-in (HR configurable)
- Integrasi mesin fingerprint Fingerspot (API/Konektor)
- Manajemen data karyawan
- Report payroll-ready (ringkasan kehadiran, lembur, cuti, izin)
- Approval workflow: cuti, izin, lembur
- Pengajuan cuti & izin dari mobile app
- Notifikasi: reminder presensi, status approval

### Out (V1 — future)
- Liveness detection / anti-spoofing advanced
- Face recognition matching
- Integrasi payroll langsung (BPJS, PPh 21, disbursement)
- Shift scheduling / penjadwalan shift
- Multi-bahasa (English, dll)
- Integrasi mesin fingerprint selain Fingerspot
- iOS app (stretch goal, not committed)
- Timesheet / rencana kerja harian

## Vision

Absenin menjadi platform workforce management terdepan di Indonesia untuk UMKM dan perusahaan menengah. Dimulai dari presensi dan pelacakan tenaga kerja campuran, berkembang ke:

- **Year 1**: Presensi + lembur + cuti/izin (core attendance)
- **Year 2**: Payroll integration (BPJS, PPh 21, disbursement) + shift scheduling + timesheet
- **Year 3**: Performance management + productivity analytics + marketplace integration (e.g., Jamsostek, asuransi)

Tidak bersaing dengan HRIS enterprise seperti SAP/SuccessFactors, tetapi menjadi pilihan utama perusahaan 20-500 karyawan yang butuh solusi simpel, terjangkau, dan mobile-first.
