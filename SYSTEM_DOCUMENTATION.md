# 📚 Dokumentasi Arsitektur & Manual Teknis Sistem
## ERP META Adhya Tirta Umbulan (Water Transmission Enterprise System)

Dokumen ini merupakan panduan arsitektur perangkat lunak (*software architecture*), justifikasi pemilihan teknologi, struktur hak akses, serta spesifikasi logika bisnis menyeluruh dari setiap modul pada sistem **ERP META Adhya Tirta Umbulan**.

> [!NOTE]
> Untuk panduan langkah-demi-langkah instalasi server produksi (*production deployment*), silakan merujuk secara khusus ke dokumen terpisah: [CARA_DEPLOY.md](file:///c:/laragon/www/Umbulan/CARA_DEPLOY.md).

---

## 📑 Daftar Isi
1. [Tujuan & Ruang Lingkup Sistem](#1-tujuan--ruang-lingkup-sistem)
2. [Arsitektur Stack Teknologi & Justifikasi Pemilihan](#2-arsitektur-stack-teknologi--justifikasi-pemilihan)
3. [Hirarki Hak Akses & Matriks Persetujuan Dinamis (Cuti, CAR, MPR)](#3-hirarki-hak-akses--matriks-persetujuan-dinamis-cuti-car-mpr)
4. [Logika Pengaturan Profil, Keamanan Data & Autentikasi Dua Tahap (2-Tier)](#4-logika-pengaturan-profil-keamanan-data--autentikasi-dua-tahap-2-tier)
5. [Logika Modul Kehadiran, Geofencing Multi-Stasiun & Biometrik Wajah Cerdas](#5-logika-modul-kehadiran-geofencing-multi-stasiun--biometrik-wajah-cerdas)
6. [Logika Modul Pengajuan Cuti, Validasi Kuota & Kalibrasi Hari Libur](#6-logika-modul-pengajuan-cuti-validasi-kuota--kalibrasi-hari-libur)
7. [Logika Modul CAR (Cash Advance) & MPR (Material Purchase)](#7-logika-modul-car-cash-advance--mpr-material-purchase)
8. [Arsitektur Self-Hosted WhatsApp Gateway (Baileys Microservice)](#8-arsitektur-self-hosted-whatsapp-gateway-baileys-microservice)
9. [Automasi Latar Belakang (Task Scheduler & Cron Jobs)](#9-automasi-latar-belakang-task-scheduler--cron-jobs)

---

## 1. Tujuan & Ruang Lingkup Sistem

**ERP META Adhya Tirta Umbulan** dirancang secara khusus untuk mendukung operasional harian perusahaan penyedia dan transmisi air bersih skala regional (Sistem Penyediaan Air Minum / SPAM Umbulan). Ruang lingkup operasional sistem mencakup dua lingkungan kerja yang memiliki karakteristik dan regulasi kerja berbeda:

```text
┌─────────────────────────────────────────────────────────────────────────────────┐
│              SISTEM PENYEDIAAN AIR MINUM (SPAM) REGIONAL UMBULAN                │
│             Transmisi Air Bersih Sepanjang 93 Km (Pasuruan - Gresik)            │
└──────────────────────────────────────┬──────────────────────────────────────────┘
                                       │
            ┌──────────────────────────┴──────────────────────────┐
            ▼                                                     ▼
┌──────────────────────────────────────┐  ┌──────────────────────────────────────┐
│       KANTOR UTAMA (ADMINISTRATIF)   │  │    STASIUN LAPANGAN (OPERASIONAL)    │
│ • Jadwal Kerja: Normal               │  │ • Jadwal Kerja: Roster 3 Shift       │
│ • 5 Hari Kerja (Senin - Jumat)       │  │ • 24/7 Continuous Operations         │
│ • Jam Operasional: 08:00 - 17:00 WIB │  │ • Durasi Shift: 12 Jam (Pagi/Malam)  │
│ • Mengikuti Tanggal Merah SKB 3 Men. │  │ • Mengabaikan Tanggal Merah Nasional │
│ • Libur Akhir Pekan (Sabtu & Minggu) │  │ • Libur Berdasarkan Jadwal Off Day   │
└──────────────────────────────────────┘  └──────────────────────────────────────┘
```

1. **Lingkungan Kerja Kantor Utama (Administratif):**
   - Beroperasi dengan jadwal kerja **Normal** (Senin s/d Jumat, 5 hari kerja, 08:00 – 17:00 WIB).
   - Menikmati hak libur akhir pekan (Sabtu & Minggu) dan seluruh Tanggal Merah Hari Libur Nasional resmi pemerintah Indonesia (SKB 3 Menteri).
2. **Lingkungan Kerja Stasiun Lapangan (Operasional Transmisi 24/7):**
   - Mengelola operasional transmisi air bersih sepanjang 93 kilometer dari mata air Umbulan (Kabupaten Pasuruan) melintasi Kota Pasuruan, Kabupaten Sidoarjo, Kota Surabaya, hingga Kabupaten Gresik.
   - Mengoperasikan 4 Stasiun Utama/Booster (Stasiun Mata Air Umbulan, Stasiun Pompa Bangil, Stasiun Pasuruan, Stasiun Surabaya) dan 18 Stasiun Rumah Meter (Offtake).
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
| :--- | :--- | :--- |
| **PHP** | `^8.3` | Menggunakan Typed Class Properties, Readonly Classes, Enumerations, dan engine JIT (Just-In-Time) compiler yang memangkas waktu eksekusi serta menghemat alokasi memori pemrosesan data. |
| **Laravel Framework** | `11.x / 12.x` | Fondasi enterprise MVC yang menyediakan keamanan bawaan (CSRF protection, PDO parameter binding pencegah SQL injection, session encryption), Task Scheduler terintegrasi, dan Eloquent ORM. |
| **Barryvdh DomPDF** | `^3.1 / ^4.0` | Mengonversi template Blade HTML ke berkas cetak PDF resmi (CAR, MPR, Surat Cuti) dengan dukungan *inline base64 image* untuk logo dan stempel tanda tangan digital tanpa dependensi Chromium/Puppeteer yang membebani RAM server. |
| **Ladumor Laravel PWA** | `^1.0` | Menyediakan Service Worker, Offline Caching, dan Web App Manifest, memungkinkan aplikasi diinstal langsung pada *smartphone* Android/iOS staf lapangan tanpa perantara Google Play / App Store. |
| **PHP GD Extension** | Bawaan PHP | Digunakan oleh [KehadiranController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Absen/KehadiranController.php) untuk membubuhkan stempel *dynamic watermark* (tanggal, jam WIB, nama karyawan, stasiun, dan status GPS) pada foto bukti alasan sebelum disimpan ke media penyimpanan publik. |
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
Setiap peran (*role*) pada tabel `roles` menyimpan konfigurasi aturan persetujuan dalam kolom JSON `approval_rules` yang dikelola melalui [RoleController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Admin/RoleController.php):

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

### B. Matriks Wewenang & Alur Persetujuan Antarmodul:

| Modul Pengajuan | Konfigurasi Rule | Pemohon | Verifikator Tahap 1 | Penyetuju Tahap 2 (Final) | Karakteristik Alur & Integritas |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Pengajuan Cuti / Izin** | `cuti` (1 / 2 Level) | Level 3 | Role Approver 1 | Role Approver 2 | **Sekuensial Berjenjang:**<br>1. Tahap 1 menyetujui $\rightarrow$ notifikasi WA otomatis terkirim ke Tahap 2.<br>2. Tahap 2 menyetujui $\rightarrow$ status akhir `approved`, kuota cuti terpotong, dan jadwal absen ter-inject otomatis.<br>3. Jika salah satu menolak $\rightarrow$ status akhir langsung `rejected`. |
| **Cash Advance (CAR)** | `car` (1 / 2 Level) | Level 3 | Role Approver 1 | Role Approver 2 | **Verifikasi Kebutuhan & Pagu Anggaran:**<br>1. Tahap 1 memverifikasi fisik kebutuhan barang operasional lapangan.<br>2. Tahap 2 menyetujui pencairan uang kas ke rekening pemohon.<br>3. Begitu `approved`, formulir cetak PDF resmi siap diunduh. |
| **Material Purchase (MPR)** | `mpr` (1 / 2 Level) | Level 3 | Role Approver 1 | Role Approver 2 | **Standardized Two-Stage Technical Approval:**<br>1. Tahap 1 (*Operation Manager*) memverifikasi urgensi teknis suku cadang/pipa.<br>2. Tahap 2 (*Director*) menyetujui alokasi pengadaan.<br>3. Terintegrasi dengan notifikasi WA langsung ke pejabat berwenang. |

### C. Prinsip Pengamanan Alur Persetujuan:
1. **Proteksi Persetujuan Mandiri (*Self-Approval Protection*):**
   - Sistem secara ketat melarang karyawan memproses persetujuan atas pengajuan yang diajukannya sendiri (`$pengajuan->user_id === $atasan->id`).
   - Query antrean persetujuan atasan secara otomatis mengecualikan dokumen milik pengguna yang sedang login (`where('user_id', '!=', $atasan->id)`), dan controller backend memblokir eksekusi aksi dengan pesan peringatan keras.
2. **Otomatisasi Persetujuan Tingkat Puncak (*Top-Level Auto Approval*):**
   - Jika pemohon merupakan pejabat puncak (*Top Level* / tidak memiliki `parent_role_id` atau tanpa approver role), pengajuan Cuti, CAR, dan MPR langsung disetujui secara otomatis (`status_akhir = 'approved'`) saat pembuatan dokumen.
3. **Mekanisme Penolakan Cepat (*First-to-Act Rejection*):**
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
Dikelola oleh [ForgotPasswordController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Auth/ForgotPasswordController.php) dengan alur aman:
1. **Identifikasi Akun:** Pengguna dapat memasukkan alamat Email atau Nomor WhatsApp terdaftar pada rute `/forgot-password`.
2. **Pilihan Saluran Pengiriman:** Sistem menyajikan pilihan pengiriman kode OTP 6-digit: via Email Kepegawaian (`PasswordResetOtpMail`) atau via WhatsApp Gateway (`WhatsAppService::sendPasswordResetOtp`). Nomor tujuan ditampilkan dalam format tersensor (*masked*).
3. **Mekanisme Perlindungan Brute-Force & Replay Attack:**
   - **Rate Limiting:** Dibatasi maksimal 5 request per IP dan 3 request per akun dalam rentang 300 detik.
   - **Cooldown:** Tombol kirim ulang dikunci selama 60 detik (*resend cooldown*).
   - **Brute Force Lockout:** Kode OTP otomatis dibatalkan jika salah memasukkan sebanyak 5 kali berturut-turut.
   - **Kedaluwarsa:** Kode OTP hanya berlaku selama 5 menit.
   - **Authorization Token:** Setelah OTP cocok, sistem menerbitkan token otorisasi acak 40 karakter di sesi (`reset_auth_token`) yang berlaku 10 menit untuk membuka form penentuan kata sandi baru, mencegah serangan pemalsuan request.

### C. Proteksi Integritas Profil: Penguncian Field Gender & Multi-Role
Pada menu pengaturan profil ([AccountController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/User/AccountController.php)):
1. **Penguncian Atribut Gender / Jenis Kelamin:**
   - Field `gender_id` **DIKUNCI SECARA PERMANEN** bagi seluruh pengguna biasa.
   - Hanya Administrator Level 1 yang berwenang mengubah jenis kelamin akun.
   - **Rasional Bisnis:** Jenis kelamin merupakan parameter penentu hak cuti khusus: Cuti Haid (2 hari/bulan) dan Cuti Melahirkan (3 bulan) hanya dialokasikan untuk gender Wanita (`gender_id = 2`). Penguncian ini menutup celah manipulasi kuota cuti.
2. **Penanganan Multi-Role Dinamis:**
   - Pengguna dapat memegang lebih dari satu peran jabatan melalui tabel relasi `role_user` dengan penanda `is_primary`.
   - Backend memblokir upaya non-admin yang mencoba menaikkan wewenangnya sendiri ke role Admin.
3. **Penugasan Multi-Stasiun Rumah Meter (Role PIPELINE):**
   - Khusus staf lapangan dengan peran `AREA (PIPELINE)`, sistem menyediakan form pemilihan multi-stasiun Rumah Meter via tabel relasi `station_user`. Staf pipeline dapat ditugaskan untuk mengawasi beberapa Rumah Meter sekaligus di sepanjang jalur pipa.

### D. Enam Kriteria Mutlak Kelayakan Pengajuan (`EnsureAccountIsComplete`)
Middleware [EnsureAccountIsComplete.php](file:///c:/laragon/www/Umbulan/app/Http/Middleware/EnsureAccountIsComplete.php) memvalidasi status kelengkapan akun sebelum mengizinkan pembuatan formulir Cuti, CAR, maupun MPR:
1. Verifikasi Email aktif (`email_verified_at != null`).
2. Nomor WhatsApp terisi dan terverifikasi (`phone_verified_at != null`).
3. Biometrik wajah telah direkam (`count(face_descriptor) === 128`).
4. Tanda tangan digital telah tersimpan (`signature != null`).
5. Jadwal kerja operasional aktif (`schedule_type` normal atau roster).

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
* Method `evaluateGeofence` pada [KehadiranController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Absen/KehadiranController.php) melakukan iterasi ke seluruh stasiun untuk menentukan `shortestDistance`. Jika $d \le \text{radius stasiun}$ (default 50 – 100 meter), pengguna dinyatakan **Di Dalam Radius**.
* Administrator dapat mengkalibrasi posisi stasiun secara visual pada halaman `/admin/stations` menggunakan pointer peta interaktif (*drag-and-drop*) atau *URL parser Google Maps*.

### B. Rotasi Shift Roster Operasional (Continuous Shift 24/7)
* Jadwal staf operasional lapangan dihitung secara algoritmik oleh `ScheduleService.php`.
* Rotasi terjadi serentak setiap **Hari Selasa pukul 07:00 WIB**:
  * **Minggu I:** Shift Pagi ($07:00 - 19:00\text{ WIB}$, durasi 12 jam kerja).
  * **Minggu II:** Shift Malam ($19:00 - 07:00\text{ WIB}$, durasi 12 jam kerja).
  * **Minggu III:** Minggu Libur Roster (*Off Day* pemulihan fisik).

### C. Pemindaian Biometrik Wajah Client-Side 128-Vektor
* Model *neural network* Face-API.js mengeksekusi arsitektur `TinyFaceDetector` (resolusi input 224x224, threshold 0.5) untuk melacak wajah dan mengekstrak 68 titik kontur (*face landmarks*).
* Jaringan `FaceRecognitionNet` memetakan wajah ke dalam **vektor 128-angka desimal floating-point (*descriptor vector*)**.
* Pencocokan dilakukan dengan menghitung jarak Euclidean (*Euclidean Distance*) antara vektor wajah video kamera saat ini ($A$) dengan vektor referensi terdaftar pada basis data ($B$):

$$\text{Distance} = \sqrt{\sum_{i=1}^{128} (A_i - B_i)^2}$$
$$\text{Confidence} = \max\left(0, \min\left(100, \text{round}\left((1 - \text{Distance}) \times 100\right)\right)\right)$$

* **Ambang Batas Toleransi (*Match Threshold*):** Ditetapkan pada $\text{Distance} \le 0.55$.
* **Keunggulan Privasi & Efisiensi:** Berkas foto selfie harian **tidak pernah diunggah atau disimpan di server**. Server hanya menyimpan string JSON array 128 angka saat inisialisasi akun.

### D. Alur Auto-Submit Presensi Cerdas
Antarmuka modal presensi pada [dashboardindex.blade.php](file:///c:/laragon/www/Umbulan/resources/views/dashboard/dashboardindex.blade.php) bekerja secara reaktif:

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
   - Method `checkIn` dan `checkOut` pada [KehadiranController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Absen/KehadiranController.php) memeriksa secara ketat:
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
Dikelola oleh [HolidayService.php](file:///c:/laragon/www/Umbulan/app/Services/HolidayService.php) yang menyimpan master data baku Surat Keputusan Bersama (SKB) 3 Menteri untuk tahun 2025, 2026, dan 2027. Service dilengkapi mekanisme caching 30 hari untuk menjamin deteksi akurat hari libur keagamaan yang sifatnya bergeser (seperti Idul Fitri, Nyepi, Waisak, dan Maulid Nabi Muhammad SAW).

### B. Perbedaan Perhitungan Hari Efektif: Normal vs Roster
Method `hitungHariKerjaEfektif` pada [PengajuanCutiController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Cuti/PengajuanCutiController.php) memperlakukan kedua kelompok kerja secara presisi dan adil:

```text
                          [ Evaluasi Tanggal Cuti ]
                                      │
                 ┌────────────────────┴────────────────────┐
                 ▼                                         ▼
         STAF JADWAL NORMAL                       STAF JADWAL ROSTER
  • Hari Sabtu & Minggu diabaikan.         • Tanggal merah nasional diabaikan.
  • Tanggal Merah SKB diabaikan.           • Cuti pada tanggal merah tetap
  • Hanya hari kerja aktif yang              memotong kuota jika hari tersebut
    memotong saldo cuti.                     merupakan dinas aktif (Pagi/Malam).
  • Peringatan jika seluruh rentang        • Cuti TIDAK MEMOTONG kuota HANYA
    adalah Hari Libur Nasional.              jika bertepatan dengan Libur Roster.
```

### C. Aturan Baku Pemotongan Saldo Cuti ([CutiHelperTrait.php](file:///c:/laragon/www/Umbulan/app/Traits/CutiHelperTrait.php))
1. **Cuti Tahunan (`CT` / ID = 4):**
   - Satu-satunya jenis cuti yang memotong kuota saldo tahunan di tabel `saldo_cutis`.
   - **Validasi Saldo Efektif:** Sistem menghitung ketersediaan saldo dengan formula:
     $$\text{Saldo Efektif} = \text{Sisa Saldo Database} - \sum \text{Hari Cuti Pending}$$
     Jika $\text{Saldo Efektif} < \text{Hari Diajukan}$, pengajuan langsung ditolak oleh sistem.
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
   - Fitur edit dan update dokumen setelah dikirimkan telah ditiadakan pada [PengajuanCarController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Car/PengajuanCarController.php). Dokumen bersifat permanen (*immutable*) guna menjaga akuntabilitas audit finansial.
4. **Dokumen Cetak PDF Resmi ([DokumenCarController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Car/DokumenCarController.php)):**
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
4. **Dokumen Cetak PDF Resmi ([DokumenMprController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Mpr/DokumenMprController.php)):**
   - Memuat layout tabel formal standar PT META Adhya Tirta Umbulan lengkap dengan 5 kotak otorisasi tanda tangan digital resmi:
     * **Requester:** Pemohon lapangan.
     * **Operation Manager:** Atasan Verifikator Tahap 1.
     * **Procurement:** Bagian Pengadaan Barang.
     * **Director:** Penyetuju Tahap 2.
     * **President Director / Executive:** Pimpinan puncak persetujuan final.
   - Dokumen yang berstatus `rejected` dilarang untuk dicetak.

---

## 8. Arsitektur Self-Hosted WhatsApp Gateway (Baileys Microservice)

Modul WhatsApp Gateway dibangun sebagai microservice Node.js mandiri yang beroperasi pada port lokal `3001` ([whatsapp-service/server.js](file:///c:/laragon/www/Umbulan/whatsapp-service/server.js)):

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
   - Soket Baileys diinisialisasi dengan konfigurasi agresif: `keepAliveIntervalMs: 15000` dan `connectTimeoutMs: 60000`.
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

Otomatisasi pemeliharaan sistem didefinisikan secara deklaratif pada [routes/console.php](file:///c:/laragon/www/Umbulan/routes/console.php) dan dipicu setiap menit oleh sistem Crontab server:

```cron
* * * * * cd /var/www/nama-proyek && php artisan schedule:run >> /dev/null 2>&1
```

### Rincian 3 Tugas Terjadwal:

| Perintah Artisan | Frekuensi & Waktu Eksekusi | Target Data & Logika Bisnis |
| :--- | :--- | :--- |
| `saldo:reset-haid` | Tanggal 1 awal bulan, **pukul 00:00 WIB** | **Reset Kuota Cuti Haid Bulanan:** Mengalokasikan kuota baru sebanyak **2 hari** untuk bulan kalender berjalan di tabel `saldo_cutis` bagi seluruh karyawan perempuan aktif (`isPerempuan()`). Kuota bulan sebelumnya tidak diakumulasikan. |
| `saldo:reset-tahunan` | Tanggal 1 Januari, **pukul 00:00 WIB** | **Inisialisasi Saldo Cuti Tahunan:** Mengalokasikan kuota baku sebanyak **12 hari kerja** untuk tahun kalender baru di tabel `saldo_cutis` bagi seluruh karyawan aktif pada jenis cuti tahunan (`CT`). |
| `pengajuan:followup-wa` | Setiap **10 menit** (Timezone: `Asia/Jakarta`) | **Pengingat WhatsApp Dokumen Pending:** Mengirim pesan follow-up berkala ke ponsel penanggung jawab persetujuan yang sedang pending (Cuti, CAR, MPR) dengan proteksi ganda. |

### Mekanisme Perlindungan `pengajuan:followup-wa`:
1. **Pemeriksaan Status Gateway:**
   - Scheduler memeriksa ketersediaan gateway via `WhatsAppService::getStatus()`. Jika gateway dalam kondisi terputus (*disconnected*), proses pemindaian langsung dibatalkan secara aman guna mencegah penumpukan antrean error.
2. **Batasan Umur Dokumen & Cooldown Anti-Spam:**
   - Hanya memproses pengajuan yang telah berumur minimal 30 menit sejak diajukan (`created_at <= now() - 30 minutes`).
   - Menerapkan batasan jeda minimal 2 jam antar pengingat (`last_notified_at <= now() - 2 hours`) agar atasan tidak dibanjiri notifikasi berulang.
3. **Penyaring Jam Kerja Atasan (*Work Hours Guard*):**
   - Scheduler memeriksa jadwal dinas penanggung jawab persetujuan via `ScheduleService::isUserWorkingNow($approver)`:
     * **Atasan Staf Normal:** Notifikasi pengingat **HANYA DIKIRIMKAN** pada hari Senin s/d Jumat pada rentang jam dinas resmi (08:00 – 17:00 WIB).
     * **Atasan Staf Roster:** Notifikasi pengingat **HANYA DIKIRIMKAN** jika atasan yang bersangkutan sedang berada di dalam jam dinas shift aktif miliknya (Shift Pagi 07:00 – 19:00 WIB atau Shift Malam 19:00 – 07:00 WIB).
     * Jika atasan sedang berada di luar jam kerja atau sedang menikmati **Hari Libur Roster (Off Day)**, pengiriman pesan otomatis ditunda demi menghormati waktu istirahat karyawan.

---

*Manual arsitektur dan spesifikasi teknis ini merupakan dokumen resmi acuan standar pengembangan, integrasi, dan audit operasional sistem ERP META Adhya Tirta Umbulan.*