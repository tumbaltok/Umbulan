# 🌊 ERP META Adhya Tirta Umbulan
### *Water Transmission Enterprise System & Operational Resource Planning*

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-^8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Face--API.js-128--Vektor-00ADD8?style=for-the-badge&logo=webgl&logoColor=white" alt="Biometrics">
  <img src="https://img.shields.io/badge/WhatsApp-Baileys%20Socket-25D366?style=for-the-badge&logo=whatsapp&logoColor=white" alt="WhatsApp Gateway">
  <img src="https://img.shields.io/badge/PWA-Enabled-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white" alt="PWA Ready">
</p>

---

Dokumen ini merupakan panduan arsitektur perangkat lunak (*software architecture*), manual teknis komprehensif, struktur hak akses, spesifikasi logika bisnis, panduan instalasi lokal, serta **panduan deployment server produksi (*bare-metal* / VPS)** resmi dari sistem **ERP META Adhya Tirta Umbulan**.

> [!NOTE]
> Seluruh panduan teknis dan operasional sistem telah terintegrasi penuh dalam dokumen ini sebagai *Single Source of Truth*. Anda dapat menggunakan Daftar Isi di bawah untuk langsung menuju bagian yang dibutuhkan.

---

## 📑 Daftar Isi

- [1. Tujuan & Ruang Lingkup Sistem](#1-tujuan--ruang-lingkup-sistem)
- [2. Arsitektur Stack Teknologi & Justifikasi Pemilihan](#2-arsitektur-stack-teknologi--justifikasi-pemilihan)
- [3. Hirarki Hak Akses & Matriks Persetujuan Dinamis](#3-hirarki-hak-akses--matriks-persetujuan-dinamis-cuti-car-mpr)
- [4. Pengaturan Profil, Keamanan Data & Autentikasi 2-Tier](#4-logika-pengaturan-profil-keamanan-data--autentikasi-dua-tahap-2-tier)
- [5. Modul Kehadiran, Geofencing Multi-Stasiun & Biometrik Wajah Cerdas](#5-logika-modul-kehadiran-geofencing-multi-stasiun--biometrik-wajah-cerdas)
- [6. Modul Pengajuan Cuti, Validasi Kuota & Kalibrasi Hari Libur](#6-logika-modul-pengajuan-cuti-validasi-kuota--kalibrasi-hari-libur)
- [7. Modul CAR (Cash Advance) & MPR (Material Purchase)](#7-logika-modul-car-cash-advance--mpr-material-purchase)
- [8. Arsitektur Self-Hosted WhatsApp Gateway (Baileys Microservice)](#8-arsitektur-self-hosted-whatsapp-gateway-baileys-microservice)
- [9. Automasi Latar Belakang (Task Scheduler & Cron Jobs)](#9-automasi-latar-belakang-task-scheduler--cron-jobs)
- [10. Panduan Instalasi Pengembangan Lokal (Local Quick Start)](#10-panduan-instalasi-pengembangan-lokal-local-quick-start)
- [11. Panduan Deployment Server Produksi (Bare-Metal / VPS)](#11-panduan-deployment-server-produksi-bare-metal--vps)
  - [11.1. Spesifikasi Server & Diagram Topologi Jaringan](#111-spesifikasi-server--diagram-topologi-jaringan)
  - [11.2. Langkah 1: Persiapan Server & Konfigurasi Firewall UFW](#112-langkah-1-persiapan-server--konfigurasi-firewall-ufw)
  - [11.3. Langkah 2: Instalasi Paket Inti Server & PHP 8.3 Ekstensi](#113-langkah-2-instalasi-paket-inti-server--php-83-ekstensi)
  - [11.4. Langkah 3: Instalasi Composer 2, Node.js LTS, dan PM2](#114-langkah-3-instalasi-composer-2-nodejs-lts-dan-pm2)
  - [11.5. Langkah 4: Setup Database Server (MySQL / MariaDB)](#115-langkah-4-setup-database-server-mysql--mariadb)
  - [11.6. Langkah 5: Kloning Repositori, Konfigurasi `.env`, dan Generate Key](#116-langkah-5-kloning-repositori-konfigurasi-env-dan-generate-key)
  - [11.7. Langkah 6: Instalasi Dependensi PHP & Kompilasi Asset Frontend](#117-langkah-6-instalasi-dependensi-php--kompilasi-asset-frontend)
  - [11.8. Langkah 7: Setup & Daemonize WhatsApp Baileys Microservice](#118-langkah-7-setup--daemonize-whatsapp-baileys-microservice)
  - [11.9. Langkah 8: Migrasi Database & Pembuatan Storage Symlink](#119-langkah-8-migrasi-database--pembuatan-storage-symlink)
  - [11.10. Langkah 9: Pengaturan Hak Akses Direktori (Permissions & Ownership)](#1110-langkah-9-pengaturan-hak-akses-direktori-permissions--ownership)
  - [11.11. Langkah 10: Konfigurasi Background Services (Supervisor & Crontab)](#1111-langkah-10-konfigurasi-background-services-supervisor--crontab)
  - [11.12. Langkah 11: Konfigurasi Web Server Nginx & SSL Let's Encrypt](#1112-langkah-11-konfigurasi-web-server-nginx--ssl-lets-encrypt)
  - [11.13. Langkah 12: Optimasi Cache & Performa Produksi](#1113-langkah-12-optimasi-cache--performa-produksi)
  - [11.14. Prosedur Pemeliharaan & Skrip Update Rutin (`deploy.sh`)](#1114-prosedur-pemeliharaan--skrip-update-rutin-deploysh)
  - [11.15. Checklist Pengujian Pasca-Deploy (Go-Live Verification)](#1115-checklist-pengujian-pasca-deploy-go-live-verification)

---

## 1. Tujuan & Ruang Lingkup Sistem

**ERP META Adhya Tirta Umbulan** dirancang secara khusus untuk mendukung operasional harian perusahaan penyedia dan transmisi air bersih skala regional (Sistem Penyediaan Air Minum / SPAM Regional Umbulan). Ruang lingkup operasional sistem mencakup lingkungan kerja administratif dan lapangan yang dibakukan ke dalam **3 Tipe Jadwal Kerja Fixed (Baku)**:

```text
┌─────────────────────────────────────────────────────────────────────────────────┐
│              SISTEM PENYEDIAAN AIR MINUM (SPAM) REGIONAL UMBULAN                │
│             Transmisi Air Bersih Sepanjang 93 Km (Pasuruan - Gresik)            │
└──────────────────────────────────────┬──────────────────────────────────────────┘
                                       │
            ┌──────────────────────────┼──────────────────────────┐
            ▼                          ▼                          ▼
┌──────────────────────┐   ┌──────────────────────┐   ┌──────────────────────┐
│   REGULER 5 HARI     │   │   REGULER 6 HARI     │   │    ROSTER / SHIFT    │
│ • Senin s/d Jumat    │   │ • Senin s/d Sabtu    │   │ • 24/7 Continuous    │
│ • 07:00 – 16:00 WIB  │   │ • Sen-Jum: 07-16 WIB │   │ • Shift Pagi: 07-19  │
│ • Libur: Sab & Min   │   │ • Sabtu: 07-12 WIB   │   │ • Shift Malam: 19-07 │
│ • Libur Nasional SKB │   │ • Libur: Minggu (OFF)│   │ • Rotasi Tiap Selasa │
│   3 Menteri (Resmi)  │   │ • Libur Nasional SKB │   │ • Mengabaikan Tanggal│
│                      │   │   3 Menteri (Resmi)  │   │   Merah Nasional     │
└──────────────────────┘   └──────────────────────┘   └──────────────────────┘
```

1. **Tipe 1: Reguler 5 Hari (`reguler_5_hari`):**
   - Diperuntukkan bagi staf kantor administratif dan unit kerja dengan pola 5 hari kerja (Senin s/d Jumat).
   - Jam kerja baku otomatis terkunci pada pukul **07:00 – 16:00 WIB**.
   - Hak libur akhir pekan pada hari Sabtu & Minggu (OFF) serta mengikuti seluruh Tanggal Merah Hari Libur Nasional resmi pemerintah Indonesia (SKB 3 Menteri).
2. **Tipe 2: Reguler 6 Hari (`reguler_6_hari`):**
   - Diperuntukkan bagi staf teknis atau pendukung lapangan dengan pola 6 hari kerja (Senin s/d Sabtu).
   - Jam kerja baku:
     * **Senin s/d Jumat:** Pukul **07:00 – 16:00 WIB**.
     * **Khusus Hari Sabtu (Setengah Hari):** Pukul **07:00 – 12:00 WIB**.
   - Hak libur pada hari Minggu (OFF) serta mengikuti seluruh Tanggal Merah Hari Libur Nasional resmi pemerintah Indonesia (SKB 3 Menteri).
3. **Tipe 3: Roster / Shift Operasional 24/7 (`roster`):**
   - Mengelola operasional transmisi air bersih sepanjang 93 kilometer dari mata air Umbulan (Kabupaten Pasuruan) melintasi Kota Pasuruan, Kabupaten Sidoarjo, Kota Surabaya, hingga Kabupaten Gresik.
   - Mengoperasikan 4 Stasiun Utama/Booster (Stasiun Mata Air Umbulan, Stasiun Pompa Bangil, Stasiun Pasuruan, Stasiun Surabaya) dan 18 Stasiun Rumah Meter (*Offtake*).
   - Beroperasi tanpa henti (*24/7 continuous operations*) dengan **Sistem Kerja Roster 3 Shift** (Minggu I Pagi, Minggu II Malam, Minggu III Libur Roster).
   - Staf operasional transmisi tidak mengenal tanggal merah libur nasional biasa demi menjamin pasokan air minum masyarakat tetap mengalir tanpa interupsi.

Sistem mengintegrasikan fungsi presensi berbasis lokasi presisi (*geofencing* multi-stasiun), pemindaian biometrik wajah *client-side* 128-vektor, *auto-submit attendance*, alur persetujuan berjenjang dinamis, pengadaan material (*MPR*), pengajuan panjar operasional (*CAR*), gerbang notifikasi pesan WhatsApp dua arah, serta automasi cron job latar belakang.

---

## 2. Arsitektur Stack Teknologi & Justifikasi Pemilihan

Aplikasi dibangun menggunakan arsitektur modular berlapis (*multi-tier architecture*) yang dirancang untuk keandalan tinggi (*high availability*), efisiensi sumber daya server, dan kemudahan skalabilitas:

```text
┌────────────────────────────────────────────────────────────────────────┐
│                          PRESENTATION TIER                             │
│  Blade Templates + Tailwind CSS v4 + Turbo Drive + SweetAlert2 + PWA  │
├────────────────────────────────────────────────────────────────────────┤
│                     CLIENT-SIDE BIOMETRIC ENGINE                       │
│     Face-API.js (TinyFaceDetector + FaceRecognitionNet 128-vektor)     │
├────────────────────────────────────────────────────────────────────────┤
│                          APPLICATION TIER                              │
│       Laravel Framework 11/12 (PHP 8.3+ FPM / Eloquent ORM / Sanctum)  │
├────────────────────────────────┬───────────────────────────────────────┤
│    EXTERNAL MICROSERVICE       │          PERSISTENCE TIER             │
│ Node.js Express + Baileys WA   │  MySQL 8.0+ / MariaDB 10.11+          │
│ (Port 3001, Persistent Socket) │  (Database Relasional Transaksional)  │
└────────────────────────────────┴───────────────────────────────────────┘
```

### Rincian Pustaka & Justifikasi Teknis:

| Komponen / Pustaka | Versi | Justifikasi Pemilihan & Fungsi Kritis |
| :--- | :---: | :--- |
| **PHP** | `^8.3` | Menggunakan Typed Class Properties, Readonly Classes, Enumerations, dan engine JIT (Just-In-Time) compiler yang memangkas waktu eksekusi serta menghemat alokasi memori pemrosesan data. |
| **Laravel Framework** | `11.x / 12.x` | Fondasi enterprise MVC yang menyediakan keamanan bawaan (CSRF protection, PDO parameter binding pencegah SQL injection, session encryption), Task Scheduler terintegrasi, dan Eloquent ORM. |
| **Barryvdh DomPDF** | `^3.1 / ^4.0` | Mengonversi template Blade HTML ke berkas cetak PDF resmi (CAR, MPR, Surat Cuti) dengan dukungan *inline base64 image* untuk logo dan stempel tanda tangan digital tanpa dependensi Chromium/Puppeteer yang membebani RAM server. |
| **Ladumor Laravel PWA** | `^1.0` | Menyediakan Service Worker, Offline Caching, dan Web App Manifest, memungkinkan aplikasi diinstal langsung pada *smartphone* Android/iOS staf lapangan tanpa perantara Google Play / App Store. |
| **PHP GD Extension** | Bawaan PHP | Digunakan oleh [KehadiranController.php](app/Http/Controllers/Absen/KehadiranController.php) untuk membubuhkan stempel *dynamic watermark* (tanggal, jam WIB, nama karyawan, stasiun, dan status GPS) pada foto bukti alasan sebelum disimpan ke media penyimpanan publik. |
| **Tailwind CSS v4** | `^4.0` | Utility-first CSS framework dengan engine modern `@tailwindcss/vite`. Menghasilkan bundel CSS sangat ringkas (< 150 KB gzip), mendukung tema *dark mode / light mode* adaptif, dan visual modern glassmorphism. |
| **Face-API.js** | Assets Lokal | Model kecerdasan buatan berbasis WebGL/TensorFlow.js yang dieksekusi **murni di peramban pengguna (*client-side*)**. Menghasilkan 128-vektor *descriptor* untuk verifikasi presensi harian tanpa perlu mengirim rekaman video atau foto selfie harian ke server database (menghemat ruang penyimpanan hingga ratusan gigabyte). |
| **Node.js & Baileys** | `^6.7` | Microservice *self-hosted* mandiri berbasis WebSocket Baileys multidevice. Menggantikan gateway berbayar pihak ketiga, menghilangkan biaya langganan bulanan per nomor, dan menjaga kerahasiaan nomor internal perusahaan. |
| **HolidayService** | Internal | Service kalender yang memuat master data resmi Surat Keputusan Bersama (SKB) 3 Menteri (Menag, Menaker, MenPAN-RB) tahun 2025–2027 dengan sistem caching 30 hari, menjamin keakuratan deteksi tanggal merah dinamis keagamaan. |

---

## 3. Hirarki Hak Akses & Matriks Persetujuan Dinamis (Cuti, CAR, MPR)

Sistem memadukan pembagian hak akses **Level Pengguna (Level 1 - 3)** dengan **Role Approval Matrix Dinamis** yang dapat dikonfigurasi per jabatan pada menu Administrator:

```text
┌────────────────────────────────────────────────────────────────────────┐
│  LEVEL 1: System Administrator & Eksekutif (BOD, GM)                  │
│  • Hak Akses: Penuh (Full Control & Bypass Operasi)                   │
│  • Wewenang: Master data, reset biometrik, konfigurasi role hierarchy, │
│              monitoring seluruh antrean approval pending              │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│  LEVEL 2: Pengawas Menengah (Manager, Kepala Bidang, Supervisor)       │
│  • Hak Akses: Monitoring / Read-Only                                  │
│  • Pengecualian: Memiliki hak mutlak Approve / Reject pada rute       │
│                  persetujuan (admin.persetujuan.*)                     │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│  LEVEL 3: Staf Pelaksana, Operator Stasiun & Pipeline                  │
│  • Hak Akses: Pemohon Pengajuan (Requester) & Pelaksana Presensi      │
│  • Dilarang masuk ke seluruh panel administratif (/admin/*)            │
└────────────────────────────────────────────────────────────────────────┘
```

### A. Konfigurasi Role Approval Matrix (`approval_rules`)
Setiap peran (*role*) pada tabel `roles` menyimpan konfigurasi aturan persetujuan dalam kolom JSON `approval_rules` yang dikelola melalui [RoleController.php](app/Http/Controllers/Admin/RoleController.php):

```json
{
  "cuti": {
    "levels": 2,
    "approver_1_role_id": 5,
    "approver_2_role_id": 2
  },
  "car": {
    "levels": 2,
    "approver_1_role_id": 5,
    "approver_2_role_id": 2
  },
  "mpr": {
    "levels": 2,
    "approver_1_role_id": 5,
    "approver_2_role_id": 2
  }
}
```

### B. Matriks Wewenang & Alur Persetujuan Antarmodul

| Modul Pengajuan | Konfigurasi Rule | Pemohon | Verifikator Tahap 1 | Penyetuju Tahap 2 (Final) | Karakteristik Alur & Integritas |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Pengajuan Cuti / Izin** | `cuti` (1 / 2 Level) | Level 3 | Role Approver 1 | Role Approver 2 | **Sekuensial Berjenjang:**<br>1. Tahap 1 menyetujui $\rightarrow$ notifikasi WA otomatis terkirim ke Tahap 2.<br>2. Tahap 2 menyetujui $\rightarrow$ status akhir `approved`, kuota cuti terpotong, dan jadwal absen ter-inject otomatis.<br>3. Jika salah satu menolak $\rightarrow$ status akhir langsung `rejected`. |
| **Cash Advance (CAR)** | `car` (1 / 2 Level) | Level 3 | Role Approver 1 | Role Approver 2 | **Verifikasi Kebutuhan & Pagu Anggaran:**<br>1. Tahap 1 memverifikasi fisik kebutuhan barang operasional lapangan.<br>2. Tahap 2 menyetujui pencairan uang kas ke rekening pemohon.<br>3. Begitu `approved`, formulir cetak PDF resmi siap diunduh. |
| **Material Purchase (MPR)** | `mpr` (1 / 2 Level) | Level 3 | Role Approver 1 | Role Approver 2 | **Standardized Two-Stage Technical Approval:**<br>1. Tahap 1 (*Operation Manager*) memverifikasi urgensi teknis suku cadang/pipa.<br>2. Tahap 2 (*Director*) menyetujui alokasi pengadaan.<br>3. Terintegrasi dengan notifikasi WA langsung ke pejabat berwenang. |

### C. Prinsip Pengamanan Alur Persetujuan
1. **Proteksi Persetujuan Mandiri (*Self-Approval Protection*):**
   - Sistem secara ketat melarang karyawan memproses persetujuan atas pengajuan yang diajukannya sendiri (`$pengajuan->user_id === $atasan->id`).
   - Query antrean persetujuan atasan secara otomatis mengecualikan dokumen milik pengguna yang sedang login (`where('user_id', '!=', $atasan->id)`), dan controller backend memblokir eksekusi aksi dengan pesan peringatan.
2. **Proteksi Kolusi Berjenjang (*Anti-Collusion Multi-Stage Guard*):**
   - Pengguna yang telah bertindak sebagai Verifikator Tahap 1 dilarang keras menyetujui kembali dokumen yang sama pada Tahap 2, memastikan fungsi *check and balance* independen.
3. **Otomatisasi Persetujuan Tingkat Puncak (*Top-Level Auto Approval*):**
   - Jika pemohon merupakan pejabat puncak (*Top Level* / tidak memiliki `parent_role_id` atau tanpa approver role), pengajuan Cuti, CAR, dan MPR langsung disetujui secara otomatis (`status_akhir = 'approved'`) saat pembuatan dokumen.
4. **Mekanisme Penolakan Cepat (*First-to-Act Rejection*):**
   - Jika dokumen ditolak pada Tahap 1 maupun Tahap 2, seluruh siklus pengajuan langsung dinyatakan gugur (`status_akhir = 'rejected'`). Penyetuju wajib menyertakan alasan pada field `catatan_penolakan`. Dokumen yang berstatus *rejected* dilarang untuk dicetak.

---

## 4. Logika Pengaturan Profil, Keamanan Data & Autentikasi Dua Tahap (2-Tier)

Sistem menerapkan arsitektur keamanan data berlapis untuk menjaga otentisitas identitas pengguna, akurasi hak kuota cuti, dan integritas transaksi:

```text
┌─────────────────────────────────────────────────────────────────────────┐
│                        SIKLUS KELAYAKAN AKUN                            │
└────────────────────────────────────┬────────────────────────────────────┘
                                     │
                 ┌───────────────────┴───────────────────┐
                 ▼                                       ▼
    ┌─────────────────────────┐             ┌─────────────────────────┐
    │  TIER 1: VERIFIKASI     │             │  TIER 2: VERIFIKASI     │
    │  EMAIL KEPEGAWAIAN      │────────────>│  WHATSAPP OTP 6-DIGIT   │
    │  (Signed URL 60 menit)  │             │  (Baileys SMS/WA 5 mnt) │
    └─────────────────────────┘             └────────────┬────────────┘
                                                         │
                                                         ▼
                                            ┌─────────────────────────┐
                                            │ KELENGKAPAN 5 KRITERIA  │
                                            │ • Email Verified        │
                                            │ • WhatsApp Verified     │
                                            │ • Biometrik 128-vektor  │
                                            │ • Tanda Tangan Digital  │
                                            │ • Jadwal Kerja Aktif    │
                                            └────────────┬────────────┘
                                                         │
                                                         ▼
                                            ┌─────────────────────────┐
                                            │ AKSES PENUH PENGAJUAN   │
                                            │ (Cuti, CAR, & MPR)      │
                                            └─────────────────────────┘
```

### A. Autentikasi Dua Tahap (2-Tier Verification)
1. **Tier 1: Verifikasi Email (`hasVerifiedEmail()`):**
   - Registrasi awal mewajibkan konfirmasi kepemilikan email perusahaan melalui *Laravel Signed URL* yang dikirimkan ke kotak masuk pengguna.
   - Middleware `verified` membatasi akses pengguna yang belum mengonfirmasi emailnya dan mengarahkannya ke rute `/auth/verify-email`.
2. **Tier 2: Verifikasi Nomor WhatsApp OTP (`hasVerifiedPhone()`):**
   - Setelah email terverifikasi, pengguna diarahkan ke rute `/auth/verify-phone`.
   - Microservice Baileys mengirimkan kode numerik OTP 6-digit ke nomor WhatsApp pengguna:
     ```text
     *[ERP META ADHYA TIRTA UMBULAN]*
     Halo [Nama Karyawan],
     Kode Verifikasi (OTP) WhatsApp Anda adalah: *123456*
     Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun.
     ```
   - Middleware `phone.verified` memastikan seluruh akses internal dashboard dan transaksi hanya dapat dibuka jika kolom `phone_verified_at` telah terisi.

### B. Pemulihan Kata Sandi Multi-Saluran (Forgot Password via Email & WA)
Dikelola oleh [ForgotPasswordController.php](app/Http/Controllers/Auth/ForgotPasswordController.php) dengan alur aman:
1. **Identifikasi Akun:** Pengguna dapat memasukkan alamat Email atau Nomor WhatsApp terdaftar pada rute `/forgot-password`.
2. **Pilihan Saluran Pengiriman:** Sistem menyajikan pilihan pengiriman kode OTP 6-digit: via Email Kepegawaian (`PasswordResetOtpMail`) atau via WhatsApp Gateway (`WhatsAppService::sendPasswordResetOtp`). Nomor tujuan ditampilkan dalam format tersensor (*masked*).
3. **Mekanisme Perlindungan Brute-Force & Replay Attack:**
   - **Rate Limiting:** Dibatasi maksimal 5 request per IP dan 3 request per akun dalam rentang 300 detik.
   - **Cooldown:** Tombol kirim ulang dikunci selama 60 detik (*resend cooldown*).
   - **Brute Force Lockout:** Kode OTP otomatis dibatalkan jika salah memasukkan sebanyak 5 kali berturut-turut.
   - **Kedaluwarsa:** Kode OTP hanya berlaku selama 5 menit.
   - **Authorization Token:** Setelah OTP cocok, sistem menerbitkan token otorisasi acak 40 karakter di sesi (`reset_auth_token`) yang berlaku 10 menit untuk membuka form penentuan kata sandi baru, mencegah pemalsuan request.

### C. Proteksi Integritas Profil: Penguncian Field Gender & Multi-Role
Pada menu pengaturan profil ([AccountController.php](app/Http/Controllers/User/AccountController.php)):
1. **Penguncian Atribut Gender / Jenis Kelamin:**
   - Field `gender_id` **DIKUNCI SECARA PERMANEN** bagi seluruh pengguna biasa.
   - Hanya Administrator Level 1 yang berwenang mengubah jenis kelamin akun.
   - **Rasional Bisnis:** Jenis kelamin merupakan parameter penentu hak cuti khusus: Cuti Haid (2 hari/bulan) dan Cuti Melahirkan (3 bulan) hanya dialokasikan untuk gender Wanita (`gender_id = 2`). Penguncian ini menutup celah manipulasi kuota cuti.
2. **Penanganan Multi-Role Dinamis:**
   - Pengguna dapat memegang lebih dari satu peran jabatan melalui tabel relasi `role_user` dengan penanda `is_primary`.
   - Backend memblokir upaya non-admin yang mencoba menaikkan wewenangnya sendiri ke role Admin.
3. **Penugasan Multi-Stasiun Rumah Meter (Role PIPELINE):**
   - Khusus staf lapangan dengan peran `AREA (PIPELINE)`, sistem menyediakan form pemilihan multi-stasiun Rumah Meter via tabel relasi `station_user`. Staf pipeline dapat ditugaskan untuk mengawasi beberapa Rumah Meter sekaligus di sepanjang jalur pipa.
4. **Standarisasi Pengaturan Jadwal Kerja Mandiri:**
   - Karyawan memilih 1 dari 3 Tipe Jadwal Kerja Baku (`reguler_5_hari`, `reguler_6_hari`, atau `roster`).
   - Jam masuk, jam pulang, dan hari kerja terkunci secara otomatis oleh sistem (*preset*) jika memilih tipe Reguler. Pemilihan tipe Roster mengaktifkan konfigurasi shift awal dan kalkulasi rotasi mingguan.

### D. Kriteria Mutlak Kelayakan Pengajuan (`EnsureAccountIsComplete`)
Middleware [EnsureAccountIsComplete.php](app/Http/Middleware/EnsureAccountIsComplete.php) memvalidasi status kelengkapan akun sebelum mengizinkan pembuatan formulir Cuti, CAR, maupun MPR:
1. Verifikasi Email aktif (`email_verified_at != null`).
2. Nomor WhatsApp terisi dan terverifikasi (`phone_verified_at != null`).
3. Biometrik wajah telah direkam (`count(face_descriptor) === 128`).
4. Tanda tangan digital telah tersimpan (`signature != null`).
5. Jadwal kerja operasional aktif (`schedule_type`: `reguler_5_hari`, `reguler_6_hari`, atau `roster`).

---

## 5. Logika Modul Kehadiran, Geofencing Multi-Stasiun & Biometrik Wajah Cerdas

### A. Geofencing Multi-Stasiun & Formula Trigonometri Haversine
Sistem memantau keberadaan staf terhadap 22 stasiun operasional resmi (4 Stasiun Pompa/Kantor + 18 Stasiun Rumah Meter). Jarak pengguna dihitung secara instan terhadap koordinat seluruh stasiun menggunakan formula lingkaran besar (*Great Circle*) **Haversine Spherical**:

$$\Delta\sigma = 2 \cdot \arcsin\left(\sqrt{\sin^2\left(\frac{\Delta\phi}{2}\right) + \cos(\phi_1)\cos(\phi_2)\sin^2\left(\frac{\Delta\lambda}{2}\right)}\right)$$
$$d = R \cdot \Delta\sigma$$

* Di mana:
  * $\phi_1, \phi_2$ adalah garis lintang (*latitude*) posisi pengguna dan stasiun dalam radian.
  * $\Delta\phi = \phi_2 - \phi_1$ dan $\Delta\lambda = \lambda_2 - \lambda_1$ (*longitude difference*).
  * $R$ adalah radius bumi rata-rata ($6.371.000\text{ meter}$).
  * $d$ adalah jarak lurus permukaan bumi dalam satuan meter.
* Method `evaluateGeofence` pada [KehadiranController.php](app/Http/Controllers/Absen/KehadiranController.php) melakukan iterasi ke seluruh stasiun untuk menentukan `shortestDistance`. Jika $d \le \text{radius stasiun}$ (default 50 – 100 meter), pengguna dinyatakan **Di Dalam Radius**.
* Administrator dapat mengkalibrasi posisi stasiun secara visual pada halaman `/admin/stations` menggunakan pointer peta interaktif (*drag-and-drop*) atau *URL parser Google Maps*.

### B. Standarisasi 3 Tipe Jadwal Kerja Operasional Baku

Sistem membakukan dan menyederhanakan konfigurasi jadwal kerja seluruh karyawan ke dalam **3 Tipe Jadwal Tetap (*Fixed Rules*)**. Konfigurasi jam masuk, jam pulang, dan hari kerja telah dikunci serta diprogram secara otomatis oleh sistem (*preset*) pada [ScheduleService.php](app/Services/ScheduleService.php) guna mencegah deviasi maupun manipulasi jam operasional manual:

#### 1. Matriks Komparasi 3 Tipe Jadwal Kerja

| Parameter | Tipe 1: Reguler 5 Hari | Tipe 2: Reguler 6 Hari | Tipe 3: Roster / Shift |
| :--- | :--- | :--- | :--- |
| **Identitas Nilai / Enum** | `reguler_5_hari` | `reguler_6_hari` | `roster` |
| **Label UI Resmi** | `Reguler (5 Hari: Senin – Jumat)` | `Reguler (6 Hari: Senin – Sabtu, Setengah Hari)` | `Roster / Shift` |
| **Hari Kerja Aktif** | Senin s/d Jumat (5 Hari Kerja) | Senin s/d Sabtu (6 Hari Kerja) | Rotasi 3 Pekan Siklis (24/7) |
| **Hari Libur (OFF)** | Sabtu & Minggu (OFF) | Minggu (OFF) | Jadwal OFF Roster (Pekan ke-3) |
| **Jam Masuk Baku** | Fixed **07:00 WIB** | Fixed **07:00 WIB** | Shift Pagi: **07:00 WIB**<br>Shift Malam: **19:00 WIB** |
| **Jam Pulang Baku** | Fixed **16:00 WIB** | Sen–Jum: **16:00 WIB**<br>Sabtu: **12:00 WIB** (Setengah Hari) | Shift Pagi: **19:00 WIB**<br>Shift Malam: **07:00 WIB** |
| **Kalibrasi Hari Libur** | Mengikuti SKB 3 Menteri | Mengikuti SKB 3 Menteri | Mengabaikan Tanggal Merah Nasional |
| **Keterlambatan (`is_late`)**| Check-in > 07:00 WIB | Check-in > 07:00 WIB | Pagi > 07:00 WIB / Malam > 19:00 WIB |
| **Pulang Awal (`isEarly`)** | Check-out < 16:00 WIB | Sen–Jum < 16:00 WIB / Sabtu < 12:00 WIB | Sesuai jam selesai shift masing-masing |

#### 2. Logika Algoritmik Backend ([ScheduleService.php](app/Services/ScheduleService.php))
1. **Reguler 5 Hari (`calculateReguler5HariSchedule`):**
   - Mengevaluasi hari berjalan melalui ISO Day of Week ($1 = \text{Senin} \dots 7 = \text{Minggu}$).
   - **Senin s/d Jumat (Hari 1–5):** `scheduled_in = '07:00:00'`, `scheduled_out = '16:00:00'`, `is_day_off = false`.
   - **Sabtu & Minggu (Hari 6 & 7):** `is_day_off = true` (Libur Akhir Pekan).
2. **Reguler 6 Hari (`calculateReguler6HariSchedule`):**
   - **Senin s/d Jumat (Hari 1–5):** `scheduled_in = '07:00:00'`, `scheduled_out = '16:00:00'`, `is_day_off = false`.
   - **Khusus Hari Sabtu (Hari 6):** `scheduled_in = '07:00:00'`, `scheduled_out = '12:00:00'` (Setengah Hari Kerja), `is_day_off = false`.
   - **Hari Minggu (Hari 7):** `is_day_off = true` (Libur Hari Minggu).
3. **Roster / Shift 24/7 (`calculateRosterByDateTime`):**
   - Diperuntukkan bagi staf operasional transmisi non-stop 24 jam dengan rotasi mingguan yang berganti serentak setiap **Hari Selasa pukul 07:00 WIB**:
     * **Pekan I:** Shift Pagi ($07:00 - 19:00\text{ WIB}$, durasi 12 jam kerja).
     * **Pekan II:** Shift Malam ($19:00 - 07:00\text{ WIB}$, durasi 12 jam kerja).
     * **Pekan III:** Minggu Libur Roster (*Off Day* pemulihan fisik).
   - Seluruh logika, struktur data rotasi 3 pekan, dan formula rotasi shift roster yang telah ada dipertahankan seutuhnya tanpa modifikasi fungsionalitas.

#### 3. Penegakan Validasi Presensi & Keterlambatan ([KehadiranController.php](app/Http/Controllers/Absen/KehadiranController.php))
- **Blokir Presensi Hari Libur:** Sistem memeriksa status `is_day_off` dari `ScheduleService::getTodaySchedule()`. Upaya absen masuk pada hari libur otomatis ditolak dengan respon: *"Hari ini adalah jadwal libur (OFF) Anda"*.
- **Evaluasi Keterlambatan Masuk:** Keterlambatan check-in dihitung saat jam presensi aktual melebihi jam masuk baku ($> 07:00\text{ WIB}$). Jika terlambat, karyawan wajib mengisi alasan tertulis dan mengunggah foto bukti berstempel watermark dinamis.
- **Evaluasi Jam Pulang:** Check-out sebelum jam pulang baku ($< 16:00\text{ WIB}$ pada hari biasa atau $< 12:00\text{ WIB}$ pada hari Sabtu untuk Reguler 6 Hari) ditandai sebagai pulang lebih awal (`isEarly = true`).

#### 4. Pengalaman Antarmuka Pengguna (UI/UX)
- **Halaman Profil Karyawan (`/profile`):**
  * Dropdown pemilihan menampilkan 3 opsi berlabel rapi dan profesional.
  * Ketika opsi Reguler 5 Hari atau Reguler 6 Hari dipilih, form input jam masuk/pulang manual dan checkbox hari kerja otomatis disembunyikan/dinonaktifkan, lalu digantikan oleh **Badge & Kartu Informasi Baku** yang menjelaskan jam operasional tetap.
  * Ketika opsi Roster dipilih, sistem secara cerdas menampilkan antarmuka pemilihan shift awal beserta pratinjau (*preview*) rotasi 3 pekan ke depan.
- **Modal Detail Karyawan Admin (`/admin/karyawan`):**
  * Dilengkapi penanda badge warna tematik: Sky Blue untuk Reguler 5 Hari, Teal untuk Reguler 6 Hari, dan Purple untuk Roster.

### C. Pemindaian Biometrik Wajah Client-Side 128-Vektor
* Model *neural network* Face-API.js mengeksekusi arsitektur `TinyFaceDetector` (resolusi input 224x224, threshold 0.5) untuk melacak wajah dan mengekstrak 68 titik kontur (*face landmarks*).
* Jaringan `FaceRecognitionNet` memetakan wajah ke dalam **vektor 128-angka desimal floating-point (*descriptor vector*)**.
* Pencocokan dilakukan dengan menghitung jarak Euclidean (*Euclidean Distance*) antara vektor wajah video kamera saat ini ($A$) dengan vektor referensi terdaftar pada basis data ($B$):

$$\text{Distance} = \sqrt{\sum_{i=1}^{128} (A_i - B_i)^2}$$
$$\text{Confidence} = \max\left(0, \min\left(100, \text{round}\left((1 - \text{Distance}) \times 100\right)\right)\right)$$

* **Ambang Batas Toleransi (*Match Threshold*):** Ditetapkan pada $\text{Distance} \le 0.55$.
* **Keunggulan Privasi & Efisiensi:** Berkas foto selfie harian **tidak pernah diunggah atau disimpan di server**. Server hanya menyimpan string JSON array 128 angka saat inisialisasi akun.

### D. Alur Auto-Submit Presensi Cerdas
Antarmuka modal presensi pada [dashboardindex.blade.php](resources/views/dashboard/dashboardindex.blade.php) bekerja secara reaktif:

```text
  [ Kamera Depan Terbuka & Model Dimuat ]
                     │
                     ▼
  [ Deteksi Wajah Realtime (Interval 300ms) ]
                     │
         ┌───────────┴───────────┐
         ▼                       ▼
   Wajah Tidak Cocok       Wajah Cocok (Distance <= 0.55)
   • Tombol Lanjut Kunci   • Buka Kunci Tombol Lanjut
   • Counter Reset = 0     • stableAttendanceFaceCount++
                                 │
                     ┌───────────┴───────────┐
                     ▼                       ▼
            stableCount < 2         stableCount >= 2
          (Tahan Posisi Wajah)      (Verifikasi Berhasil Stabil)
                                             │
                       ┌─────────────────────┴─────────────────────┐
                       ▼                                           ▼
             DI DALAM RADIUS & TEPAT WAKTU             DI LUAR RADIUS / TERLAMBAT
             • Jeda 800ms + Animasi Loading            • Kunci Auto-Submit
             • Auto-Submit Presensi ke Backend         • Tampilkan Form Alasan Wajib
             • Kamera Ditutup Otomatis                 • Wajib Unggah Bukti Berstempel
```

### E. Strict Backend Biometric Guard & Proteksi Anti-Cheating
1. **Validasi Ketat di Sisi Backend:**
   - Method `checkIn` dan `checkOut` pada [KehadiranController.php](app/Http/Controllers/Absen/KehadiranController.php) memeriksa:
     * Apakah data `face_descriptor` pengguna di database bernilai null atau panjang elemennya $\ne 128$. Jika tidak valid, request langsung ditolak dengan status **HTTP 422 Unprocessable Entity**.
     * Apakah request membawa parameter boolean `is_face_verified === true`. Upaya bypass melalui manipulasi API / script luar otomatis digagalkan.
2. **Perekaman Mandiri 1x Seumur Hidup Akun:**
   - Karyawan hanya diizinkan merekam data wajah mandiri tepat 1x saat melengkapi profil akun.
   - Begitu kolom `face_descriptor` terisi, endpoint `POST /user/face/register` mengunci akses dan merespons **HTTP 403 Forbidden**.
3. **Otoritas Reset Biometrik Khusus Admin Level 1:**
   - Tombol **"Reset Biometrik"** pada modal detail karyawan (`POST /admin/karyawan/{id}/reset-biometric`) hanya dapat dieksekusi oleh Administrator Level 1 untuk mengosongkan kembali kolom `face_descriptor = null` apabila karyawan mengalami perubahan fisik signifikan.

### F. Dynamic Watermarking pada Berkas Bukti Presensi
Jika karyawan terpaksa presensi di luar radius stasiun atau terlambat karena kendala lapangan, unggahan foto bukti alasan diproses menggunakan ekstensi PHP GD:
* Menghitung resolusi gambar dan menyesuaikan ukuran font secara proporsional.
* Menempelkan kotak latar semi-transparan (*bounding box*) di sudut bawah gambar.
* Membubuhkan teks stempel permanen: Nama Karyawan, NIP, Tanggal & Jam WIB, Nama Stasiun Terdekat, Jarak Meter GPS, dan Status Verifikasi Biometrik Wajah.

---

## 6. Logika Modul Pengajuan Cuti, Validasi Kuota & Kalibrasi Hari Libur

### A. Kalibrasi Akurasi Hari Libur Nasional (SKB 3 Menteri)
Dikelola oleh [HolidayService.php](app/Services/HolidayService.php) yang menyimpan master data baku Surat Keputusan Bersama (SKB) 3 Menteri untuk tahun 2025, 2026, dan 2027. Service dilengkapi mekanisme caching 30 hari untuk menjamin deteksi akurat hari libur keagamaan yang sifatnya dinamis (seperti Idul Fitri, Nyepi, Waisak, dan Maulid Nabi Muhammad SAW).

### B. Perbedaan Perhitungan Hari Efektif: Reguler vs Roster
Method `hitungHariKerjaEfektif` pada [PengajuanCutiController.php](app/Http/Controllers/Cuti/PengajuanCutiController.php) memperlakukan ketiga kelompok kerja secara presisi dan adil:

```text
                                 [ Evaluasi Tanggal Cuti ]
                                             │
             ┌───────────────────────────────┼───────────────────────────────┐
             ▼                               ▼                               ▼
    REGULER 5 HARI (SEN-JUM)        REGULER 6 HARI (SEN-SAB)              ROSTER / SHIFT
• Sabtu & Minggu diabaikan (OFF).• Hanya Minggu diabaikan (OFF).  • Tanggal merah nasional diabaikan.
• Tanggal Merah SKB diabaikan.   • Sabtu kerja aktif (setengah).  • Cuti pada tanggal merah tetap
• Hanya hari kerja aktif yang    • Tanggal Merah SKB diabaikan.     memotong kuota jika hari tersebut
  memotong saldo cuti.           • Hanya hari kerja aktif yang      merupakan dinas aktif (Pagi/Malam).
• Peringatan jika seluruh          memotong saldo cuti.           • Cuti TIDAK MEMOTONG kuota HANYA
  rentang adalah Libur Nasional.                                    jika bertepatan dgn Libur Roster.
```

### C. Aturan Baku Pemotongan Saldo Cuti ([CutiHelperTrait.php](app/Traits/CutiHelperTrait.php))
1. **Cuti Tahunan (`CT` / ID = 4):**
   - Satu-satunya jenis cuti yang memotong kuota saldo tahunan di tabel `saldo_cutis`.
   - **Validasi Saldo Efektif:** Sistem menghitung ketersediaan saldo dengan formula:
     $$\text{Saldo Efektif} = \text{Sisa Saldo Database} - \sum \text{Hari Cuti Pending}$$
     Jika $\text{Saldo Efektif} < \text{Hari Diajukan}$, pengajuan langsung ditolak oleh sistem.
   - **Proteksi Idempoten Anti-Double Deduction:** Pemotongan saldo di database diproteksi oleh flag `is_cut_saldo`, mencegah saldo terpotong lebih dari satu kali jika terjadi pengulangan sinkronisasi persetujuan.
2. **Cuti Non-Tahunan (Sakit, Melahirkan, Haid, Menikah, Duka/Kematian):**
   - **DILARANG KERAS MEMOTONG SALDO CUTI TAHUNAN.**
   - Cuti-cuti ini hanya memvalidasi batasan peruntukannya masing-masing (misal: Cuti Melahirkan selama 3 bulan, Cuti Menikah selama 3 hari kerja).
3. **Regulasi Cuti Haid Bulanan (Khusus Karyawan Perempuan):**
   - Hak kuota Cuti Haid dibatasi maksimal **2 hari per bulan kalender**.
   - Sistem secara otomatis menghitung akumulasi cuti haid yang berstatus `pending` dan `approved` pada bulan berjalan. Pengajuan yang melebihi batas 2 hari ditolak dengan pesan peringatan resmi.
   - Dashboard karyawan wanita menampilkan widget khusus sisa kuota cuti haid bulan berjalan.
4. **Injeksi Otomatis ke Jadwal Presensi:**
   - Begitu pengajuan Cuti berstatus `approved`, sistem mengeksekusi `sinkronisasiCutiDanAbsen` untuk membuat record kehadiran berstatus `cuti` pada rentang tanggal tersebut, sehingga karyawan tidak tercatat alpa (*absent*).

---

## 7. Logika Modul CAR (Cash Advance) & MPR (Material Purchase)

### A. Cash Advance Request (CAR)
Modul panjar operasional untuk kebutuhan perjalanan dinas mendesak atau pembelian perlengkapan kerja:
1. **Format Nomor Dokumen Baku:**
   `[Nomor Urut] / META / PAS / CAR / [Bulan Romawi] / [Tahun]` (Contoh: `12 / META / PAS / CAR / VIII / 2026`).
2. **Validasi Multi-Item:**
   - Pemohon wajib menginput: Alasan Pembelian, Catatan Penjelasan (*Note Explanation*), dan Nomor Rekening Penerima Pencairan Kas (*Receiving Account*).
   - Tabel rincian belanja multi-item: Nama Barang, Jumlah (Kuantitas), Satuan, Estimasi Harga Satuan, Biaya Ongkos Kirim (*Ongkir*), Kalkulasi Otomatis Total Harga (`(jumlah * estimasi_harga) + ongkir`), serta unggahan berkas proposal/nota pendukung (PDF/JPG max 2MB).
3. **Imutabilitas Dokumen Pengajuan:**
   - Fitur edit dan update dokumen setelah dikirimkan ditiadakan pada [PengajuanCarController.php](app/Http/Controllers/Car/PengajuanCarController.php). Dokumen bersifat permanen (*immutable*) guna menjaga akuntabilitas audit finansial.
4. **Dokumen Cetak PDF Resmi ([DokumenCarController.php](app/Http/Controllers/Car/DokumenCarController.php)):**
   - Dibuat menggunakan DomPDF dalam format kertas A4 Portrait.
   - Memuat logo perusahaan berstempel Base64, tabel rincian item belanja, total pengeluaran, serta kotak tanda tangan digital para pihak (Pemohon, Approver Tahap 1, Approver Tahap 2, dan Direktur).

### B. Material Purchase Request (MPR)
Modul pengadaan material suku cadang teknis stasiun, pompa transmisi, klorin, atau perbaikan kebocoran pipa:
1. **Format Nomor Dokumen Baku:**
   `[Nomor Urut] / META / PAS / MPR / [Bulan Romawi] / [Tahun]` (Contoh: `05 / META / PAS / MPR / VIII / 2026`).
2. **Parameter Pengadaan Lengkap:**
   - Tingkat Prioritas: `Normal`, `Urgent`, atau `Emergency`.
   - Departemen (default: *Operation*), *Delivery Point* (lokasi penerimaan barang di Site Umbulan atau Rumah Meter tertentu), Tanggal MPR Terakhir, Keperluan Urgensi, serta Berkas Bukti Kerusakan Fisik.
   - Tabel Rincian Material: Nama Barang, Keterangan/Spesifikasi Teknis Item, Kuantitas, Satuan, dan Estimasi Harga.
3. **Alur Persetujuan Berjenjang Dinamis:**
   - Beroperasi mengikuti konfigurasi `approval_rules->mpr` pada role pemohon (1 level atau 2 level) dengan pemberitahuan WhatsApp otomatis ke ponsel atasan.
   - Catatan penolakan wajib disertakan jika atasan menolak pengajuan.
4. **Dokumen Cetak PDF Resmi ([DokumenMprController.php](app/Http/Controllers/Mpr/DokumenMprController.php)):**
   - Memuat layout tabel formal standar PT META Adhya Tirta Umbulan lengkap dengan 5 kotak otorisasi tanda tangan digital resmi:
     * **Requester:** Pemohon lapangan.
     * **Operation Manager:** Atasan Verifikator Tahap 1.
     * **Procurement:** Bagian Pengadaan Barang.
     * **Director:** Penyetuju Tahap 2.
     * **President Director / Executive:** Pimpinan puncak persetujuan final.
   - Dokumen yang berstatus `rejected` dilarang untuk dicetak.

---

## 8. Arsitektur Self-Hosted WhatsApp Gateway (Baileys Microservice)

Modul WhatsApp Gateway dibangun sebagai microservice Node.js mandiri yang beroperasi pada port lokal `3001` ([whatsapp-service/server.js](whatsapp-service/server.js)):

```text
[ Laravel ERP System ]
         │
    HTTP POST (127.0.0.1:3001/send-message)
         │
[ Express.js WhatsApp Microservice ]
         │
┌────────┴────────────────────────────────────────┐
│  • Mutex Promise Queue (Jeda 500ms per pesan)   │
│  • Keep-Alive Heartbeat (15s Ping Interval)     │
│  • Baileys Socket (@whiskeysockets/baileys)    │
│  • Sesi Multi-File Auth (auth_session/)         │
└────────┬────────────────────────────────────────┘
         │
    WebSocket Enkripsi End-to-End
         │
[ WhatsApp Official Servers ] ───> [ Ponsel Karyawan / Atasan ]
```

### A. Karakteristik Kestabilan Koneksi (*Zero Random Disconnect*):
1. **Persistent Socket & Cacheable Signal Key Store:**
   - Soket Baileys diinisialisasi dengan konfigurasi: `keepAliveIntervalMs: 15000` dan `connectTimeoutMs: 60000`.
   - Menggunakan pustaka `makeCacheableSignalKeyStore` agar transaksi enkripsi multi-kunci perangkat multidevice tidak merusak integritas berkas sesi di media penyimpanan.
2. **Penanganan Pemutusan Koneksi Terarah (*Strict Reconnection Handler*):**
   - Direktori sesi kredensial (`whatsapp-service/auth_session/`) **TIDAK PERNAH DIHAPUS** pada status error jaringan sementara (status 408 timeout, 428 connectionClosed, 500 server error, atau status **515 restartRequired**).
   - **Kasus Khusus Status 515 (Stream Restart / Pairing Handshake):** Begitu QR Code selesai di-scan, Baileys memerlukan *stream restart* instan untuk menuntaskan pertukaran kunci enkripsi. Microservice menangani ini dengan memicu rekoneksi instan dalam 500ms tanpa menghapus sesi.
   - Sesi kredensial **HANYA BOLEH DIHAPUS** jika:
     * Administrator secara manual menekan tombol **"Putuskan Koneksi / Logout"** pada dashboard `/admin/whatsapp`.
     * Server WhatsApp mengembalikan status error autentikasi permanen (`DisconnectReason.loggedOut` / status 401 atau `badSession`).
3. **Mutex & Sequential Message Queue:**
   - Seluruh pengiriman pesan dibungkus dalam antrean sekuensial (*promise chain*) dengan jeda 500ms antar pesan. Hal ini mencegah tabrakan soket (*race conditions*), *socket flooding*, dan pemblokiran nomor oleh pihak WhatsApp akibat transmisi massal simultan.

### B. REST API Endpoints Microservice:
* `GET /status`: Mengembalikan status koneksi real-time (`connected`, `connecting`, `disconnected`), nomor telepon gateway yang terhubung, dan waktu aktif (*uptime*). Di-cache 10 detik oleh Laravel via `WhatsAppService::getStatusCached()`.
* `GET /qr`: Menghasilkan representasi string Data URL gambar QR Code untuk dipindai oleh admin.
* `POST /send-message`: Menerima muatan JSON `{ "number": "0812...", "message": "..." }`, menormalisasi awalan nomor ke format internasional `62xxx`, dan memasukkannya ke dalam Mutex Queue.
* `POST /disconnect`: Memutuskan soket secara aman, membersihkan direktori `auth_session/`, dan menginisialisasi ulang soket baru yang siap menyajikan QR Code baru.

---

## 9. Automasi Latar Belakang (Task Scheduler & Cron Jobs)

Otomatisasi pemeliharaan sistem didefinisikan secara deklaratif pada [routes/console.php](routes/console.php) dan dipicu setiap menit oleh sistem Crontab server:

```cron
* * * * * cd /var/www/nama-proyek && php artisan schedule:run >> /dev/null 2>&1
```

### Rincian 3 Tugas Terjadwal:

| Perintah Artisan | Frekuensi & Waktu Eksekusi | Target Data & Logika Bisnis |
| :--- | :---: | :--- |
| `saldo:reset-haid` | Tanggal 1 awal bulan, **pukul 00:00 WIB** | **Reset Kuota Cuti Haid Bulanan:** Mengalokasikan kuota baru sebanyak **2 hari** untuk bulan kalender berjalan di tabel `saldo_cutis` bagi seluruh karyawan perempuan aktif (`isPerempuan()`). Kuota bulan sebelumnya tidak diakumulasikan. |
| `saldo:reset-tahunan` | Tanggal 1 Januari, **pukul 00:00 WIB** | **Inisialisasi Saldo Cuti Tahunan:** Mengalokasikan kuota baku sebanyak **12 hari kerja** untuk tahun kalender baru di tabel `saldo_cutis` bagi seluruh karyawan aktif pada jenis cuti tahunan (`CT`). |
| `pengajuan:followup-wa` | Setiap **10 menit** (Timezone: `Asia/Jakarta`) | **Pengingat WhatsApp Dokumen Pending:** Mengirim pesan follow-up berkala ke ponsel penanggung jawab persetujuan yang sedang pending (Cuti, CAR, MPR) dengan proteksi ganda. |

### Mekanisme Perlindungan `pengajuan:followup-wa`:
1. **Pemeriksaan Status Gateway:**
   - Scheduler memeriksa ketersediaan gateway via `WhatsAppService::getStatus()`. Jika gateway dalam kondisi terputus (*disconnected*), proses pemindaian dibatalkan secara aman guna mencegah penumpukan antrean error.
2. **Batasan Umur Dokumen & Cooldown Anti-Spam:**
   - Hanya memproses pengajuan yang telah berumur minimal 30 menit sejak diajukan (`created_at <= now() - 30 minutes`).
   - Menerapkan batasan jeda minimal 2 jam antar pengingat (`last_notified_at <= now() - 2 hours`) agar atasan tidak dibanjiri notifikasi berulang.
3. **Penyaring Jam Kerja Atasan (*Work Hours Guard*):**
   - Scheduler memeriksa jadwal dinas penanggung jawab persetujuan via `ScheduleService::isUserWorkingNow($approver)`:
     * **Atasan Staf Reguler (5 atau 6 Hari):** Notifikasi pengingat **HANYA DIKIRIMKAN** pada hari dinas aktif (Senin–Jumat untuk Reguler 5 Hari, atau Senin–Sabtu untuk Reguler 6 Hari) pada rentang jam dinas resmi.
     * **Atasan Staf Roster:** Notifikasi pengingat **HANYA DIKIRIMKAN** jika atasan yang bersangkutan sedang berada di dalam jam dinas shift aktif miliknya (Shift Pagi 07:00 – 19:00 WIB atau Shift Malam 19:00 – 07:00 WIB).
     * Jika atasan sedang berada di luar jam kerja, akhir pekan (OFF), atau sedang menikmati **Hari Libur Roster (Off Day)**, pengiriman pesan otomatis ditunda demi menghormati waktu istirahat karyawan.

---

## 10. Panduan Instalasi Pengembangan Lokal (Local Quick Start)

Untuk menjalankan proyek di lingkungan pengembangan lokal (misalnya menggunakan Laragon, XAMPP, atau PHP CLI):

### 1. Kloning Repositori
```bash
git clone https://github.com/tumbaltok/Umbulan.git
cd Umbulan
```

### 2. Instalasi Dependensi PHP & JavaScript
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan (`.env`)
```bash
cp .env.example .env
php artisan key:generate
```
Sesuaikan parameter koneksi database MySQL pada `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=umbulan_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi Database & Symlink Storage
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Menjalankan Microservice WhatsApp Baileys
```bash
cd whatsapp-service
npm install
node server.js
```
*(Microservice akan aktif di `http://127.0.0.1:3001`)*

### 6. Menjalankan Server Pengembangan Laravel & Asset Vite
Buka terminal terpisah di direktori utama:
```bash
# Terminal 1: Asset compiler
npm run dev

# Terminal 2: Web Server Laravel
php artisan serve
```
Akses aplikasi melalui peramban: `http://localhost:8000`.

---

## 11. Panduan Deployment Server Produksi (Bare-Metal / VPS)

Panduan teknis resmi implementasi dan deployment proyek **ERP META Adhya Tirta Umbulan** dari server kosong (*bare-metal* / VPS / Cloud Server berbasis **Ubuntu 22.04 LTS** atau **Ubuntu 24.04 LTS**) hingga berjalan penuh, aman, stabil, terisolasi, dan teroptimasi secara online menggunakan domain publik bersertifikat SSL HTTPS.

> [!IMPORTANT]
> **Kebijakan Keamanan Kredensial Produksi (Zero Sensitive Data Leak):**
> Seluruh sintaks dan konfigurasi dalam panduan ini secara ketat menggunakan format placeholder yang aman (seperti `domain-anda.com`, `nama_database_anda`, dan `password_database_kuat`). Dilarang keras melakukan commit atau menyebarkan kredensial asli produksi, kunci enkripsi aplikasi (`APP_KEY`), maupun password database ke repositori publik.

### 11.1. Spesifikasi Server & Diagram Topologi Jaringan

#### A. Diagram Topologi Arsitektur Produksi
```text
                     [ Pengguna / Peramban Web / Ponsel PWA ]
                                       │
                        HTTPS (Port 443) / Let's Encrypt
                                       │
                             [ Firewall UFW ]
                  (Hanya Buka Port 22 SSH, 80 HTTP, 443 HTTPS)
                                       │
                             [ Nginx Web Server ]
                     (Reverse Proxy, Static Assets, SSL)
                                       │
                      ┌────────────────┴────────────────┐
                      ▼                                 ▼
             [ PHP 8.3-FPM Socket ]         [ Berkas Aset Statis ]
           /run/php/php8.3-fpm.sock         (public/build/, storage/)
                      │
            [ Laravel 11/12 ERP Engine ]
                      │
       ┌──────────────┼───────────────────────────────┐
       ▼              ▼                               ▼
[ MySQL 8.0+ ]  [ Supervisor Worker ]     [ WhatsApp Gateway Baileys ]
 Port 3306      queue:work (Database)       Node.js Express (Port 3001)
(127.0.0.1)     Asynchronous Jobs          Hanya Buka di 127.0.0.1 (Lokal)
       ▲                                              ▲
       │                                              │
 [ Crontab ] ──> php artisan schedule:run ────────────┘
                (Reset Haid, Tahunan, Follow-Up WA)
```

#### B. Spesifikasi Minimum & Rekomendasi Hardware Server:
* **Sistem Operasi:** Ubuntu 22.04 LTS (*Jammy Jellyfish*) atau Ubuntu 24.04 LTS (*Noble Numbat*) 64-bit.
* **Processor (CPU):** Minimal 2 vCPU (Disarankan 4 vCPU agar proses kompilasi asset Vite dan enkripsi WebSocket Baileys tidak mengalami lonjakan beban CPU).
* **Memori RAM:** Minimal 2 GB RAM + 2 GB Swap (Disarankan 4 GB RAM tanpa swap agar tidak terjadi *Out-of-Memory* / OOM Killer saat menjalankan build asset dan background worker bersamaan).
* **Ruang Penyimpanan (Storage):** Minimal 25 GB SSD / NVMe (untuk menampung berkas aplikasi, lampiran nota CAR/MPR, berkas foto bukti presensi berstempel, dan riwayat log rotasi harian).
* **Jaringan (Networking):** Alamat IP Publik Statis (*Static Public IPv4*) dengan Domain aktif yang sudah diarahkan (*DNS A Record*).

---

### 11.2. Langkah 1: Persiapan Server & Konfigurasi Firewall UFW

Lakukan koneksi ke server melalui terminal SSH:

```bash
ssh username@ip-server-anda
```

#### 1. Perbarui Indeks Repositori & Paket Sistem
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip zip software-properties-common ca-certificates lsb-release gnupg ufw
```
* **Tujuan / Kegunaan:** Memperbarui seluruh kernel dan pustaka keamanan OS Ubuntu ke versi terbaru serta memasang alat utilitas dasar jaringan dan kompresi file.
* **Dampak Jika Terlewat:** Server rentan terhadap celah keamanan lawas (*zero-day exploits*), dan perintah manipulasi arsip (`unzip`, `curl`) akan gagal dieksekusi oleh Composer.

#### 2. Amankan Port Jaringan Menggunakan UFW (Uncomplicated Firewall)
```bash
# 1. Atur kebijakan default lalu lintas jaringan
sudo ufw default deny incoming
sudo ufw default allow outgoing

# 2. Buka port esensial untuk publik
sudo ufw allow 22/tcp comment 'SSH Remote Access'
sudo ufw allow 80/tcp comment 'HTTP Web Traffic'
sudo ufw allow 443/tcp comment 'HTTPS SSL Web Traffic'

# 3. Aktifkan Firewall
sudo ufw --force enable
sudo ufw status verbose
```
* **Tujuan / Kegunaan:** Mengunci seluruh pintu masuk server dari serangan luar, kecuali port 22 (administrasi SSH), port 80 (validasi SSL Let's Encrypt), dan port 443 (akses web HTTPS resmi).
* **Dampak Jika Terlewat:** Port internal seperti **Port 3001 (WhatsApp Microservice)** dan **Port 3306 (MySQL)** dapat diakses langsung oleh peretas dari internet publik. Dengan konfigurasi ini, Port 3001 secara mutlak terlindungi karena hanya dapat diakses internal oleh aplikasi Laravel (`127.0.0.1:3001`).

---

### 11.3. Langkah 2: Instalasi Paket Inti Server & PHP 8.3 Ekstensi

Framework Laravel pada sistem ini membutuhkan PHP versi 8.3 dengan ekstensi-ekstensi spesifik untuk pengolahan gambar, basis data, dan manipulasi teks.

#### 1. Tambahkan Repository Resmi PPA Ondřej Surý
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

#### 2. Pasang PHP 8.3-FPM Beserta Seluruh Ekstensi Wajib
```bash
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath \
    php8.3-curl php8.3-gd php8.3-zip php8.3-intl php8.3-sqlite3
```

#### Tabel Rincian & Justifikasi Ekstensi PHP:
| Nama Ekstensi | Fungsi & Peran Kritis pada ERP Umbulan | Risiko Fatal Jika Terlewat |
| :--- | :--- | :--- |
| **`php8.3-fpm`** | FastCGI Process Manager untuk melayani request dari Nginx. | Nginx menghasilkan *502 Bad Gateway*. |
| **`php8.3-mysql`** | Driver PDO MySQL untuk transaksi data operasional dan presensi. | Error database: *Driver [mysql] not found*. |
| **`php8.3-gd`** | **Wajib:** Menggambar stempel watermark teks dinamis (tanggal, jam, koordinat, nama) pada foto bukti izin/presensi sebelum disimpan. | Pengajuan absen di luar radius/terlambat melempar *500 Internal Server Error* (`Call to undefined function imagecreatefromjpeg()`). |
| **`php8.3-mbstring`** | Pemrosesan string multibyte, enkripsi sesi, dan hashing akun. | Laravel gagal memproses session dan hashing password. |
| **`php8.3-xml`** | Parser XML yang dibutuhkan oleh engine dokumen `Barryvdh DomPDF`. | Pencetakan dokumen CAR, MPR, dan Surat Izin Cuti gagal ter-render (*Fatal XML Error*). |
| **`php8.3-bcmath`** | Perhitungan matematika presisi tinggi (kalkulasi biaya CAR & MPR). | Galat kalkulasi desimal anggaran belanja item. |
| **`php8.3-curl`** | Komunikasi HTTP client ke microservice Baileys WhatsApp (`127.0.0.1:3001`). | Notifikasi WhatsApp OTP dan approval gagal dikirim (*cURL error 7*). |
| **`php8.3-zip`** | Ekstraksi paket instalasi pustaka Composer dan dokumen lampiran. | Instalasi pustaka via Composer gagal mengekstrak arsip. |
| **`php8.3-intl`** | Lokalisasi penanggalan bahasa Indonesia resmi (Carbon Locale `id`). | Format nama hari dan bulan pada surat cetak resmi menjadi bahasa Inggris. |
| **`php8.3-sqlite3`** | Eksekusi automated test suite (*in-memory testing database*). | Pengujian `php artisan test` gagal berjalan. |

Verifikasi bahwa PHP-FPM telah berjalan:
```bash
sudo systemctl status php8.3-fpm --no-pager
php -v
```

---

### 11.4. Langkah 3: Instalasi Composer 2, Node.js LTS, dan PM2

#### 1. Pasang Composer 2 (PHP Dependency Manager)
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
composer --version
```

#### 2. Pasang Node.js 20/22 LTS & PM2 (Process Manager)
Aplikasi membutuhkan Node.js untuk dua fungsi vital:
1. Menjalankan engine kompilasi Vite (`@tailwindcss/vite` & Tailwind CSS v4).
2. Runtime microservice mandiri WhatsApp Baileys pada direktori `whatsapp-service/`.

```bash
# Tambahkan repository NodeSource LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Pasang PM2 secara global untuk manajemen background service WhatsApp
sudo npm install -g pm2

node -v    # Memastikan versi v20.x.x
npm -v     # Memastikan versi 10.x.x
pm2 -v     # Memastikan PM2 terpasang
```

---

### 11.5. Langkah 4: Setup Database Server (MySQL / MariaDB)

#### 1. Instalasi MySQL Server
```bash
sudo apt install -y mysql-server
sudo systemctl enable --now mysql
```

Jalankan skrip pengamanan instalasi MySQL:
```bash
sudo mysql_secure_installation
```
*(Pilih opsi `Y` untuk menghapus akun anonim, menonaktifkan login root jarak jauh, dan menghapus database test).*

#### 2. Buat Database & User Khusus ERP Umbulan
Masuk ke prompt MySQL:
```bash
sudo mysql -u root -p
```

Eksekusi perintah SQL berikut (ganti placeholder dengan nama database, user, dan password aman pilihan Anda):
```sql
CREATE DATABASE nama_database_anda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nama_user_database'@'127.0.0.1' IDENTIFIED BY 'password_database_kuat';
GRANT ALL PRIVILEGES ON nama_database_anda.* TO 'nama_user_database'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

---

### 11.6. Langkah 5: Kloning Repositori, Konfigurasi `.env`, dan Generate Key

Direktori baku penempatan aplikasi web di sistem operasi Linux Ubuntu adalah `/var/www/`.

#### 1. Kloning Repositori Proyek
```bash
cd /var/www
sudo git clone https://github.com/tumbaltok/Umbulan.git umbulan
cd /var/www/umbulan
```

#### 2. Konfigurasi File Lingkungan Produksi (`.env`)
Salin berkas template environment:
```bash
cp .env.example .env
nano .env
```

Sesuaikan baris-baris kunci berikut agar sesuai dengan lingkungan server produksi:

```dotenv
# ===================================================
# KONFIGURASI APLIKASI UTAMA
# ===================================================
APP_NAME="ERP META Adhya Tirta Umbulan"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domain-anda.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Jakarta

# ===================================================
# LOGGING SISTEM
# ===================================================
LOG_CHANNEL=daily
LOG_LEVEL=info

# ===================================================
# KONEKSI DATABASE UTAMA (MySQL)
# ===================================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=nama_user_database
DB_PASSWORD=password_database_kuat

# ===================================================
# SESSION, CACHE & DATABASE QUEUE
# ===================================================
SESSION_DRIVER=database
SESSION_LIFETIME=43200
SESSION_ENCRYPT=false

CACHE_STORE=database
QUEUE_CONNECTION=database

# ===================================================
# FILESYSTEM STORAGE (PUBLIC DISK)
# ===================================================
FILESYSTEM_DISK=public

# ===================================================
# INTEGRASI MICROSERVICE WHATSAPP GATEWAY (BAILEYS)
# ===================================================
WHATSAPP_SERVICE_URL=http://127.0.0.1:3001

# ===================================================
# LAYANAN SURAT ELEKTRONIK (SMTP PRODUCTION)
# ===================================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=username_smtp_anda
MAIL_PASSWORD=password_smtp_anda
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@domain-anda.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> [!WARNING]
> **BAHAYA KRITIS:** Pastikan parameter `APP_DEBUG=false` pada server produksi! Jika dibiarkan `true`, saat terjadi galat teknis framework akan menampilkan variabel `.env`, password database, dan kunci rahasia secara terang-terangan kepada publik di browser.

Simpan perubahan file di nano dengan menekan `Ctrl + O`, lalu `Enter`, kemudian keluar dengan `Ctrl + X`.

#### 3. Generate Application Key (Kunci Enkripsi)
```bash
php artisan key:generate --force
```

---

### 11.7. Langkah 6: Instalasi Dependensi PHP & Kompilasi Asset Frontend

#### 1. Instalasi Dependensi PHP (Composer Production)
```bash
cd /var/www/umbulan
composer install --no-dev --optimize-autoloader --no-interaction
```

#### 2. Instalasi Dependensi Frontend & Kompilasi Vite (Tailwind CSS v4)
```bash
npm ci
npm run build
```

---

### 11.8. Langkah 7: Setup & Daemonize WhatsApp Baileys Microservice

Microservice WhatsApp Gateway terletak di subfolder `whatsapp-service/` dan berjalan sebagai service Node.js mandiri berbasis soket WebSocket multidevice.

#### 1. Instalasi Dependensi Microservice
```bash
cd /var/www/umbulan/whatsapp-service
npm ci
```

#### 2. Jalankan Microservice Menggunakan PM2 di Bawah User `www-data`
```bash
# Pastikan folder auth_session tersedia
mkdir -p /var/www/umbulan/whatsapp-service/auth_session

# Jalankan server menggunakan PM2 di bawah user web server www-data
sudo -u www-data pm2 start server.js --name "umbulan-whatsapp" --time

# Simpan daftar proses aktif agar bertahan setelah server reboot
sudo -u www-data pm2 save

# Daftarkan skrip PM2 ke systemd Linux
sudo pm2 startup systemd -u www-data --hp /var/www
```

#### 3. Uji Endpoint Internal Microservice
Verifikasi bahwa microservice telah mendengarkan request pada port lokal `3001`:
```bash
curl http://127.0.0.1:3001/status
```
* **Output yang Diharapkan:**
  `{"success":true,"online":true,"status":"disconnected","phone":null,"qr":null,"uptime":...}`

---

### 11.9. Langkah 8: Migrasi Database & Pembuatan Storage Symlink

Kembali ke direktori utama aplikasi:
```bash
cd /var/www/umbulan
```

#### 1. Eksekusi Migrasi Database & Seeding Data Baku
```bash
php artisan migrate --force --seed
```
* Parameter `--force` wajib diberikan untuk mengonfirmasi operasi pada mode `APP_ENV=production`.

#### 2. Buat Symlink Storage Publik
```bash
php artisan storage:link
```
* Membuat tautan simbolis (*symbolic link*) dari direktori `storage/app/public` menuju `public/storage` agar seluruh upload lampiran CAR/MPR dan bukti foto presensi dapat ditampilkan.

---

### 11.10. Langkah 9: Pengaturan Hak Akses Direktori (Permissions & Ownership)

Web server Nginx dan PHP 8.3-FPM di Ubuntu beroperasi menggunakan akun sistem dan grup **`www-data`**. Konfigurasi hak akses kepemilikan yang tepat sangat krusial untuk keamanan dan kelancaran operasi upload:

```bash
cd /var/www/umbulan

# 1. Alokasikan kepemilikan seluruh berkas dan folder ke www-data
sudo chown -R www-data:www-data /var/www/umbulan

# 2. Atur izin standar: Direktori 755 (rwxr-xr-x) dan File 644 (rw-r--r--)
sudo find /var/www/umbulan -type d -exec chmod 755 {} \;
sudo find /var/www/umbulan -type f -exec chmod 644 {} \;

# 3. Berikan izin tulis penuh (775) khusus pada folder dinamis
sudo chmod -R 775 /var/www/umbulan/storage
sudo chmod -R 775 /var/www/umbulan/bootstrap/cache
sudo chmod -R 775 /var/www/umbulan/whatsapp-service/auth_session
```

---

### 11.11. Langkah 10: Konfigurasi Background Services (Supervisor & Crontab)

#### 1. Konfigurasi Queue Worker Menggunakan Supervisor
Supervisor bertugas menjaga proses `php artisan queue:work` tetap berjalan terus-menerus dan otomatis meregenerasi proses jika terjadi kebocoran memori (*memory leak*).

Pasang Supervisor:
```bash
sudo apt install -y supervisor
```

Buat file konfigurasi worker ERP Umbulan:
```bash
sudo nano /etc/supervisor/conf.d/umbulan-worker.conf
```

Isi dengan konfigurasi teruji berikut:
```ini
[program:umbulan-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/umbulan/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/umbulan/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Aktifkan konfigurasi Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start umbulan-worker:*
sudo supervisorctl status
```

#### 2. Konfigurasi Otomatisasi Task Scheduler Menggunakan Crontab
Buka crontab milik user sistem `www-data`:
```bash
sudo crontab -u www-data -e
```

Sisipkan baris perintah berikut pada baris paling bawah:
```cron
* * * * * cd /var/www/umbulan && php artisan schedule:run >> /dev/null 2>&1
```

* Memicu Task Scheduler Laravel setiap menit untuk mengeksekusi: `saldo:reset-haid`, `saldo:reset-tahunan`, dan `pengajuan:followup-wa`.

---

### 11.12. Langkah 11: Konfigurasi Web Server Nginx & SSL Let's Encrypt

#### 1. Pasang Nginx & Certbot
```bash
sudo apt install -y nginx certbot python3-certbot-nginx
```

#### 2. Buat Konfigurasi Virtual Host Nginx
```bash
sudo nano /etc/nginx/sites-available/umbulan.conf
```

Salin template konfigurasi *hardened-production* berikut (ganti `domain-anda.com` dengan nama domain resmi Anda):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name domain-anda.com;

    # Pengalihan otomatis seluruh lalu lintas HTTP ke HTTPS
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name domain-anda.com;

    root /var/www/umbulan/public;
    index index.php index.html;

    charset utf-8;

    # Batas ukuran upload file (diperlukan untuk lampiran dokumen CAR/MPR/Bukti Foto)
    client_max_body_size 50M;

    # Lokasi berkas log web server
    access_log /var/log/nginx/umbulan_access.log;
    error_log /var/log/nginx/umbulan_error.log error;

    # HTTP Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Penanganan Routing Utama Framework Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Caching Agresif untuk Aset Statis (Build Vite, Font, Gambar)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|webp)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    # FastCGI PHP 8.3-FPM Handler
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    # Proteksi Keamanan: Blokir Akses Langsung ke Berkas Sensitif
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* /(composer\.(json|lock)|package(-lock)?\.json|\.env.*)$ {
        deny all;
    }
}
```

#### 3. Aktifkan Site & Uji Sintaks Konfigurasi Nginx
```bash
# Buat symlink ke sites-enabled
sudo ln -s /etc/nginx/sites-available/umbulan.conf /etc/nginx/sites-enabled/

# Hapus virtual host default bawaan Nginx
sudo rm -f /etc/nginx/sites-enabled/default

# Uji validitas sintaks
sudo nginx -t
```
* **Output yang Wajib Muncul:**
  `nginx: the configuration file /etc/nginx/nginx.conf syntax is ok`
  `nginx: configuration file /etc/nginx/nginx.conf test is successful`

Muat ulang service Nginx:
```bash
sudo systemctl reload nginx
```

#### 4. Pasang Sertifikat SSL Gratis (Certbot Let's Encrypt)
Pastikan DNS A Record domain Anda (`domain-anda.com`) telah mengarah ke alamat IP server publik, lalu jalankan:

```bash
sudo certbot --nginx -d domain-anda.com
```

---

### 11.13. Langkah 12: Optimasi Cache & Performa Produksi

Framework Laravel menyediakan fitur kompilasi konfigurasi untuk menghilangkan pembacaan disk berulang pada setiap request web:

```bash
cd /var/www/umbulan

# 1. Bersihkan seluruh cache lama
php artisan optimize:clear

# 2. Kompilasi konfigurasi, rute, dan events
php artisan optimize

# 3. Kompilasi template antarmuka Blade
php artisan view:cache
```

---

### 11.14. Prosedur Pemeliharaan & Skrip Update Rutin (`deploy.sh`)

Untuk mempermudah proses rilis fitur baru dari repository Git tanpa harus mengetikkan puluhan perintah secara manual, buatlah skrip pemeliharaan otomatis berikut di `/var/www/umbulan/deploy.sh`:

```bash
sudo nano /var/www/umbulan/deploy.sh
```

Isi dengan skrip otomasi produksi berikut:

```bash
#!/usr/bin/env bash
set -e

echo "🚀 [1/9] Mengaktifkan Mode Pemeliharaan (Maintenance Mode)..."
php artisan down --render="errors::503" --secret="bypass-kunci-rahasia-anda"

echo "📥 [2/9] Menarik perubahan kode terbaru dari Git..."
git pull origin main

echo "📦 [3/9] Memperbarui dependensi PHP (Composer)..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🎨 [4/9] Membangun asset frontend Vite & Tailwind CSS v4..."
npm ci
npm run build

echo "🤖 [5/9] Memeriksa dependensi microservice WhatsApp..."
cd whatsapp-service
npm ci
cd ..

echo "🗄️ [6/9] Menjalankan migrasi database baru..."
php artisan migrate --force

echo "⚡ [7/9] Mengoptimalkan cache konfigurasi, rute, dan view..."
php artisan optimize:clear
php artisan optimize
php artisan view:cache

echo "🔄 [8/9] Memuat ulang proses latar belakang (PM2 & Supervisor)..."
sudo -u www-data pm2 restart umbulan-whatsapp
sudo supervisorctl restart umbulan-worker:*

echo "🌐 [9/9] Mematikan Mode Pemeliharaan..."
php artisan up

echo "✅ Deployment update selesai! Aplikasi ERP Umbulan telah kembali beroperasi normal."
```

Berikan izin eksekusi (*executable permission*) pada file:
```bash
sudo chmod +x /var/www/umbulan/deploy.sh
```

#### Cara Eksekusi Update Rutin di Masa Mendatang:
Kapan pun ada pembaruan kode baru dari repositori GitHub, Anda cukup login ke server dan mengeksekusi tepat satu baris perintah berikut:
```bash
cd /var/www/umbulan && sudo ./deploy.sh
```

---

### 11.15. Checklist Pengujian Pasca-Deploy (Go-Live Verification)

Setelah seluruh langkah deployment selesai, lakukan audit pengujian fungsional berikut:

- [ ] **Aksesibilitas Web & SSL:** Buka `https://domain-anda.com` di browser. Pastikan ikon gembok SSL HTTPS berstatus valid (*Secure Connection*).
- [ ] **Tampilan Antarmuka (CSS & Fonts):** Pastikan tampilan halaman modern, styling Tailwind CSS v4 ter-render rapi, dan font *Instrument Sans* termuat tanpa cacat.
- [ ] **Penautan WhatsApp Gateway:**
  - Login sebagai akun Administrator Level 1.
  - Masuk ke menu **WhatsApp Gateway** (`/admin/whatsapp`).
  - Pindai QR Code menggunakan aplikasi WhatsApp resmi perusahaan.
  - Pastikan status koneksi beralih menjadi `Connected` dengan nomor telepon yang sesuai.
  - Lakukan uji pengiriman pesan teks melalui formulir uji coba (*Test Message Box*).
- [ ] **Pengujian Alur Presensi Biometrik & Geofencing:**
  - Buka modal presensi di dashboard karyawan.
  - Izinkan akses kamera dan lokasi peramban.
  - Verifikasi bahwa model biometrik wajah termuat, mendeteksi kontur wajah, menghitung jarak Euclidean, dan memicu *auto-submit* saat berada di dalam radius stasiun.
- [ ] **Pemeriksaan Log Sistem:**
  Pastikan tidak terdapat pesan kesalahan kritis pada berkas log:
  ```bash
  # Log aplikasi Laravel
  tail -n 50 /var/www/umbulan/storage/logs/laravel-$(date +%Y-%m-%d).log

  # Log microservice Baileys WhatsApp
  sudo -u www-data pm2 logs umbulan-whatsapp --lines 50

  # Log antrean background worker Supervisor
  tail -n 50 /var/www/umbulan/storage/logs/queue-worker.log
  ```

---

*Manual teknis, arsitektur sistem, dan panduan deployment ini merupakan dokumen resmi acuan standar pengembangan, integrasi, dan audit operasional sistem ERP META Adhya Tirta Umbulan.*