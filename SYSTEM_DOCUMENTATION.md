# 📚 Dokumentasi Arsitektur & Manual Teknis Sistem
## ERP META Adhya Tirta Umbulan (Water Transmission Enterprise System)

Dokumen ini merupakan panduan arsitektur perangkat lunak (*software architecture*), justifikasi pemilihan teknologi, struktur hak akses, serta spesifikasi logika bisnis dari setiap modul pada sistem **ERP META Adhya Tirta Umbulan**.

> [!NOTE]
> Untuk panduan langkah-demi-langkah instalasi server produksi (*production deployment*), silakan merujuk secara khusus ke dokumen terpisah: [CARA_DEPLOY.md](file:///c:/laragon/www/Umbulan/CARA_DEPLOY.md).

---

## 📑 Daftar Isi
1. [Tujuan & Ruang Lingkup Sistem](#1-tujuan--ruang-lingkup-sistem)
2. [Arsitektur Stack Teknologi & Justifikasi Pemilihan](#2-arsitektur-stack-teknologi--justifikasi-pemilihan)
3. [Hirarki Hak Akses & Matriks Persetujuan (Level 1 - 4)](#3-hirarki-hak-akses--matriks-persetujuan-level-1---4)
4. [Logika Modul Kehadiran, Geofencing & Biometrik Wajah](#4-logika-modul-kehadiran-geofencing--biometrik-wajah)
5. [Logika Modul Pengajuan Cuti & Kalibrasi Hari Libur](#5-logika-modul-pengajuan-cuti--kalibrasi-hari-libur)
6. [Logika Modul CAR (Cash Advance) & MPR (Material Purchase)](#6-logika-modul-car-cash-advance--mpr-material-purchase)
7. [Arsitektur Self-Hosted WhatsApp Gateway (Baileys Microservice)](#7-arsitektur-self-hosted-whatsapp-gateway-baileys-microservice)
8. [Automasi Latar Belakang (Task Scheduler & Cron Jobs)](#8-automasi-latar-belakang-task-scheduler--cron-jobs)

---

## 1. Tujuan & Ruang Lingkup Sistem

**ERP META Adhya Tirta Umbulan** dirancang secara khusus untuk mendukung operasional harian perusahaan penyedia dan transmisi air bersih skala regional (Sistem Penyediaan Air Minum / SPAM Umbulan). Ruang lingkup operasional sistem mencakup dua lingkungan kerja yang memiliki karakteristik berbeda:

1. **Lingkungan Kerja Kantor Utama (Administratif):**
   - Beroperasi dengan jadwal kerja **Normal** (Senin s/d Jumat, 5 hari kerja, 08:00 – 17:00 WIB).
   - Menikmati hak libur akhir pekan dan seluruh Tanggal Merah Hari Libur Nasional resmi pemerintah Indonesia.
2. **Lingkungan Kerja Stasiun Lapangan (Operasional Transmisi 24/7):**
   - Mengelola operasional transmisi air bersih sepanjang 93 kilometer dari mata air Umbulan (Pasuruan) hingga ke kota/kabupaten pengguna (Surabaya, Sidoarjo, Gresik).
   - Beroperasi tanpa henti (24/7 continuous operations) dengan **Sistem Kerja Roster 3 Shift** (Pagi, Malam, Libur Roster).
   - Staf operasional tidak mengenal tanggal merah libur nasional biasa demi memastikan kontinuitas distribusi air masyarakat.

Sistem mengintegrasikan fungsi presensi berbasis lokasi presisi (*geofencing*), pemindaian biometrik wajah mandiri di peramban, pengajuan cuti berjenjang, pengadaan material darurat (*MPR*), pengajuan uang muka kas operasional (*CAR*), serta notifikasi pesan WhatsApp otomatis langsung ke telepon genggam penanggung jawab terkait.

---

## 2. Arsitektur Stack Teknologi & Justifikasi Pemilihan

Aplikasi dibangun menggunakan kombinasi teknologi modern yang dirancang untuk keandalan tinggi (*high availability*), efisiensi konsumsi memori server, dan kecepatan respons klien:

```text
┌────────────────────────────────────────────────────────────────────────┐
│                          PRESENTATION TIER                             │
│  Blade Templates + Tailwind CSS v4 + Turbo Drive + SweetAlert2 + PWA  │
├────────────────────────────────────────────────────────────────────────┤
│                     CLIENT-SIDE BIOMETRIC ENGINE                       │
│     Face-API.js (TinyFaceDetector + FaceRecognitionNet 128-vektor)     │
├────────────────────────────────────────────────────────────────────────┤
│                          APPLICATION TIER                              │
│         Laravel Framework 11/12 (PHP 8.3+ FPM / Eloquent ORM)          │
├────────────────────────────────┬───────────────────────────────────────┤
│    EXTERNAL MICROSERVICE       │          PERSISTENCE TIER             │
│ Node.js Express + Baileys WA   │  MySQL 8.0+ / MariaDB 10.11+          │
│ (Port 3001, Persistent Socket) │  (Database Relasional Transaksional)  │
└────────────────────────────────┴───────────────────────────────────────┘
```

### Rincian Pustaka & Justifikasi Teknis:

| Komponen / Pustaka | Versi | Justifikasi Pemilihan & Fungsi Kritis |
| :--- | :--- | :--- |
| **PHP** | `^8.3` | Menggunakan fitur Typed Class Properties, Readonly Classes, Enumerations, serta peningkatan performa engine JIT (Just-In-Time) compiler yang menghemat konsumsi memori pemrosesan data besar. |
| **Laravel Framework** | `11.x / 12.x` | Fondasi enterprise MVC yang menyediakan keamanan bawaan (CSRF protection, SQL injection prevention via PDO binding, session management), antrean latar belakang (*database queue*), dan Task Scheduler terpusat. |
| **Barryvdh DomPDF** | `^3.1 / ^4.0` | Mengonversi template Blade HTML ke dokumen cetak PDF resmi untuk formulir CAR, MPR, dan Surat Izin Cuti tanpa membutuhkan dependensi browser eksternal yang berat (seperti Puppeteer/Chromium). |
| **Ladumor Laravel PWA** | `^1.0` | Menyediakan arsitektur Progressive Web App (PWA) lengkap dengan Service Worker dan Web App Manifest, memungkinkan aplikasi diinstal langsung pada smartphone Android/iOS staf lapangan menyerupai aplikasi *native*. |
| **PHP GD Extension** | Bawaan PHP | Digunakan oleh modul presensi untuk membubuhkan stempel *watermark* dinamis (tanggal, jam WIB, nama karyawan, dan status verifikasi) pada foto bukti lampiran izin sebelum disimpan ke media penyimpanan. |
| **Tailwind CSS v4** | `^4.0` | Utility-first CSS framework dengan engine kompilasi modern `@tailwindcss/vite`. Menghasilkan berkas CSS produksi yang sangat ringkas (< 150 KB gzip), mendukung dark mode adaptif, dan visual antarmuka modern (glassmorphism). |
| **Face-API.js** | CDN / Assets | Model inferensi kecerdasan buatan berbasis WebGL/TensorFlow.js yang dieksekusi **murni di sisi browser pengguna (*client-side*)**. Menghasilkan 128-vektor descriptor wajah untuk pencocokan presensi tanpa perlu mengirim foto selfie harian ke server database (menghemat storage puluhan gigabyte). |
| **Node.js & Baileys** | `^6.7` | Menggantikan gateway berbayar pihak ketiga (Fonnte) dengan microservice mandiri *self-hosted* berbasis WebSocket Baileys multidevice. Menghilangkan biaya langganan bulanan dan menjaga privasi komunikasi internal perusahaan. |
| **HolidayService** | Internal | Service kalender yang memuat dataset baku Surat Keputusan Bersama (SKB) 3 Menteri (Menag, Menaker, MenPAN-RB) tahun 2025–2027 dengan sistem caching 30 hari, menjamin keakuratan deteksi tanggal merah keagamaan yang sifatnya dinamis (seperti Maulid Nabi Muhammad SAW). |

---

## 3. Hirarki Hak Akses & Matriks Persetujuan (Level 1 - 4)

Sistem menggunakan hirarki role terstruktur 4 level untuk memisahkan wewenang administratif, pengawasan, dan pelaksana:

```text
┌─────────────────────────────────────────────────────────────┐
│  LEVEL 1: System Administrator & Eksekutif (BOD, GM)        │
│  - Hak Akses: Penuh (Full Control & Bypass)                 │
│  - Wewenang: Master data, reset biometrik, approval final   │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│  LEVEL 2: Manajemen Menengah (Manager, Kepala Sektor)       │
│  - Wewenang: Persetujuan Akhir (Tahap 2) Cuti & CAR         │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│  LEVEL 3: Pengawas Lapangan & HR (Supervisor, HRD, Humas)    │
│  - Wewenang: Persetujuan Awal (Tahap 1) Verifikasi Lapangan │
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│  LEVEL 4: Staf Pelaksana & Operator Transmisi               │
│  - Wewenang: Pemohon Pengajuan (Requester) & Presensi       │
└─────────────────────────────────────────────────────────────┘
```

### Matriks Wewenang & Alur Persetujuan:

| Jenis Pengajuan | Pemohon | Verifikator Tahap 1 | Penyetuju Akhir (Tahap 2) | Karakteristik Alur |
| :--- | :---: | :---: | :---: | :--- |
| **Pengajuan Cuti / Izin** | Level 4 | **Supervisor** (Level 3) | **Manager / Admin** (Level 2/1) | **Sekuensial Berjenjang**: Tahap 1 menyetujui $\rightarrow$ notifikasi WA terkirim ke Tahap 2 $\rightarrow$ Tahap 2 menyetujui $\rightarrow$ Kuota cuti terpotong dan otomatis ter-inject ke jadwal absen. Jika pemohon Level 1/2, otomatis *auto-approved*. |
| **Cash Advance (CAR)** | Level 4 | **Supervisor** (Level 3) | **Manager / Admin** (Level 2/1) | **Verifikasi Kebutuhan**: Level 3 memverifikasi fisik kebutuhan barang lapangan $\rightarrow$ Level 1/2 menyetujui pencairan dana uang muka $\rightarrow$ Formulir PDF resmi terbit. |
| **Material Purchase (MPR)** | Level 4 | *Bypass / Parallel* | **Level 1, Level 2, atau Level 3** | **Direct Parallel Approval**: Didesain untuk kebutuhan darurat stasiun pompa/pipa bocor. Blast WA terkirim ke semua atasan sekaligus. Siapa pun atasan yang menekan Approve pertama kali, status langsung menjadi `approved`. |

---

## 4. Logika Modul Kehadiran, Geofencing & Biometrik Wajah

### A. Geofencing Multi-Stasiun & Formula Haversine
Perusahaan memiliki 22 titik lokasi operasional yang tersebar di sepanjang rute pipa transmisi air (4 Stasiun Pompa Utama + 18 Stasiun Rumah Meter). 

Jarak pengguna dihitung secara real-time terhadap koordinat stasiun penugasan menggunakan **Rumus Trigonometri Haversine Spherical**:

$$\Delta\sigma = 2 \cdot \arcsin\left(\sqrt{\sin^2\left(\frac{\Delta\phi}{2}\right) + \cos(\phi_1)\cos(\phi_2)\sin^2\left(\frac{\Delta\lambda}{2}\right)}\right)$$
$$d = R \cdot \Delta\sigma$$

* Di mana $R$ adalah jari-jari bumi ($6.371.000\text{ meter}$).
* Jika $d \le \text{radius stasiun}$ (default 50 – 100 meter), pengguna dikategorikan berada **Di Dalam Radius**.
* Jika $d > \text{radius stasiun}$ atau jam presensi melewati jam jadwal, sistem mewajibkan pengisian alasan dan unggah bukti foto berstempel *watermark*.

### B. Rotasi Shift Roster Operasional (Continuous Shift 24/7)
* Dipicu otomatis setiap **Hari Selasa pukul 07:00 WIB** melalui kalkulasi minggu modular pada `ScheduleService.php`.
* **Siklus 3 Minggu Berputar:**
  * **Minggu I:** Shift Pagi ($07:00 - 19:00\text{ WIB}$, durasi 12 jam).
  * **Minggu II:** Shift Malam ($19:00 - 07:00\text{ WIB}$, durasi 12 jam).
  * **Minggu III:** Minggu Libur Roster (*Off Day* pemulihan fisik).
* Jadwal ini independen dari kalender libur nasional guna menjamin stasiun air selalu beroperasi 24 jam nonstop.

### C. Pemindaian Biometrik Wajah Tanpa Simpan Foto
* Model *neural network* mengekstrak 68 titik landmark wajah (*Face Landmarks*) dan mengonversinya menjadi **128-angka desimal (*descriptor vector*)**.
* Pencocokan dilakukan dengan menghitung jarak Euclidean (*Euclidean Distance*) antara vektor wajah saat ini dengan vektor wajah referensi pada database:

$$\text{Distance} = \sqrt{\sum_{i=1}^{128} (A_i - B_i)^2}$$

* Ambang batas toleransi ditetapkan pada $0.50$ (setara akurasi kemiripan $\ge 90\%$).
* **Keuntungan Arsitektur:** Server tidak pernah menyimpan berkas foto selfie harian, menjaga privasi biometrik karyawan dan menghemat kapasitas hard drive server hingga 99%.

### D. Anti-Cheating Biometric Lock (Proteksi Kecurangan)
1. **Perekaman Mandiri 1x Seumur Hidup Akun:**
   - Karyawan hanya dapat merekam data wajah saat inisialisasi akun pertama kali.
   - Begitu kolom `face_descriptor` terisi, antarmuka dashboard dan menu profil otomatis beralih menjadi badge terkunci: `🔒 Biometrik Terkunci / Aktif`.
   - Endpoint `POST /user/face/register` di controller memblokir request lanjutan dengan respon **HTTP 403 Forbidden**.
2. **Otoritas Reset Khusus Admin (Level 1):**
   - Hanya Administrator Level 1 yang memiliki tombol **"Reset Biometrik"** pada modal detail karyawan (`/admin/karyawan`).
   - Fitur ini mengosongkan kembali kolom `face_descriptor = null` jika karyawan mengalami perubahan fisik permanen (misal: penggunaan kacamata resep baru/operasi wajah), memberi kesempatan tepat 1x rekam ulang.

---

## 5. Logika Modul Pengajuan Cuti & Kalibrasi Hari Libur

### A. Kalibrasi Akurasi Hari Libur Nasional (SKB 3 Menteri)
* Menggunakan `HolidayService.php` yang menyimpan master data resmi Surat Keputusan Bersama 3 Menteri untuk tahun 2025, 2026, dan 2027.
* Memastikan tanggal-tanggal keagamaan yang bergeser (seperti 17 Agustus Hari Kemerdekaan RI dan 25 Agustus 2026 Maulid Nabi Muhammad SAW) terdeteksi presisi sebagai **Hari Libur Resmi / Tanggal Merah**.

### B. Perbedaan Perhitungan Hari Efektif: Normal vs Roster
Method `hitungHariKerjaEfektif` pada [PengajuanCutiController.php](file:///c:/laragon/www/Umbulan/app/Http/Controllers/Cuti/PengajuanCutiController.php) memperlakukan kedua kelompok kerja secara adil:

1. **Staf Normal:**
   * Akhir pekan (Sabtu & Minggu) serta seluruh Hari Libur Nasional **tidak dihitung memotong kuota**.
   * Jika staf mengajukan cuti pada tanggal yang seluruhnya merupakan tanggal merah/libur, sistem menampilkan peringatan:  
     `⚠️ Rentang tanggal yang Anda pilih bertepatan dengan Hari Libur Resmi / Tanggal Merah dan tidak memotong kuota.`
2. **Staf Roster (Continuous Shift 24/7):**
   * Mengabaikan tanggal merah nasional. Cuti pada tanggal merah tetap memotong kuota jika hari tersebut merupakan jadwal dinas aktif (Pagi/Malam).
   * Cuti **tidak memotong kuota** hanya jika bertepatan dengan **Hari Libur Roster (Off Day)** staf yang bersangkutan.
   * Jika staf mengajukan cuti pada hari libur rosternya, sistem menampilkan peringatan:  
     `⚠️ Rentang tanggal yang Anda pilih bertepatan dengan Hari Libur Roster (Off Day) Anda dan tidak memotong kuota.`

### C. Aturan Baku Saldo Cuti (Proteksi Cuti Non-Tahunan)
* Sesuai ketentuan ketenagakerjaan, method `alurPotongSaldo` pada [CutiHelperTrait.php](file:///c:/laragon/www/Umbulan/app/Traits/CutiHelperTrait.php) secara ketat memisahkan jenis cuti:
  * **Cuti Tahunan (`CT`):** Memotong kuota saldo tahunan di tabel `saldo_cutis`.
  * **Cuti Non-Tahunan (Cuti Sakit, Cuti Haid, Cuti Melahirkan, Cuti Menikah, Cuti Duka/Kematian):** **DILARANG KERAS MEMOTONG SALDO CUTI TAHUNAN**. Izin-izin ini hanya memvalidasi batasan khusus (contoh: Cuti Haid maksimal 2 hari/bulan) dan saldo tahunan karyawan tetap terjaga utuh.

---

## 6. Logika Modul CAR (Cash Advance) & MPR (Material Purchase)

### A. Cash Advance Request (CAR)
* Ditujukan untuk pengajuan panjar operasional perjalanan dinas atau belanja barang operasional mendesak.
* Mendukung input *multi-item* dinamis (nama barang, kuantitas, taksiran harga, spesifikasi, dan lampiran nota/proposal).
* Persetujuan berjenjang:
  1. **Supervisor (Level 3):** Memverifikasi relevansi fisik operasional di lapangan.
  2. **Manager / Admin (Level 2/1):** Menyetujui batas pagu anggaran pencairan kas.
* Menghasilkan dokumen cetak resmi PDF berformat baku lengkap dengan tabel rincian item anggaran dan tanda tangan digital para pihak.

### B. Material Purchase Request (MPR)
* Ditujukan untuk pengadaan material fisik suku cadang stasiun, pompa, klorin, atau perbaikan pipa transmisi air yang bocor.
* Mengadopsi **Direct Parallel Approval**:
  * Pengajuan langsung masuk secara paralel ke antrean semua atasan (Level 1, 2, dan 3) sekaligus.
  * Dilengkapi notifikasi WhatsApp instan ke seluruh atasan terkait.
  * Siapa pun atasan pertama yang memberikan respon (Approve/Reject), status pengajuan langsung tereksekusi tanpa hambatan birokrasi berjenjang demi mencegah berhentinya distribusi air bersih ke masyarakat.

---

## 7. Arsitektur Self-Hosted WhatsApp Gateway (Baileys Microservice)

Modul WhatsApp Gateway dibangun sebagai microservice Node.js mandiri yang bertindak sebagai penghubung komunikasi real-time antara sistem ERP dan nomor WhatsApp karyawan:

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

### Karakteristik Kestabilan Koneksi (Zero Random Disconnect):
1. **Heartbeat Agresif & Cache Key Store:**
   - Soket Baileys dikonfigurasi dengan `keepAliveIntervalMs: 15000` dan `connectTimeoutMs: 60000` untuk menjaga koneksi WebSocket tetap aktif tanpa terputus saat idle.
   - Menggunakan `makeCacheableSignalKeyStore` dari Baileys agar transaksi enkripsi multi-kunci tidak merusak data sesi di disk.
2. **Strict Disconnect Protection:**
   - Direktori sesi (`whatsapp-service/auth_session/`) **tidak pernah dihapus secara otomatis** saat terjadi *stream restart*, jaringan timeout, atau gangguan server sementara (status error 408, 428, 440, 500, 515).
   - Sesi **HANYA BISA DIHAPUS** jika Administrator Level 1 secara manual mengklik tombol **"Putuskan Koneksi / Logout"** pada halaman dashboard `/admin/whatsapp`.
3. **Mutex & Sequential Message Queue:**
   - Seluruh pengiriman pesan dibungkus dalam antrean sekuensial (*promise chain*) dengan jeda 500ms antar pesan. Hal ini mencegah *socket congestion*, *rate-limiting*, dan pemblokiran nomor oleh WhatsApp akibat pengiriman massal beruntun.

---

## 8. Automasi Latar Belakang (Task Scheduler & Cron Jobs)

Otomatisasi pemeliharaan sistem didefinisikan secara deklaratif pada file [routes/console.php](file:///c:/laragon/www/Umbulan/routes/console.php) dan dipicu setiap menit oleh sistem Crontab server:

```cron
* * * * * cd /var/www/nama-proyek && php artisan schedule:run >> /dev/null 2>&1
```

### Rincian 3 Tugas Terjadwal:

1. **Reset Saldo Cuti Haid Bulanan (`saldo:reset-haid`):**
   * **Jadwal:** Tanggal 1 setiap awal bulan, pukul **00:00 WIB**.
   * **Fungsi:** Mengosongkan akumulasi cuti haid bulan lalu bagi seluruh karyawan wanita dan mereset hak kuota menjadi 2 hari untuk bulan kalender baru.
2. **Reset Saldo Cuti Tahunan (`saldo:reset-tahunan`):**
   * **Jadwal:** Tanggal 1 Januari setiap tahun, pukul **00:00 WIB**.
   * **Fungsi:** Memperbarui tahun kalender berjalan dan mengalokasikan saldo kuota cuti tahunan baru (12 hari kerja) bagi seluruh karyawan aktif.
3. **Follow-Up Notifikasi WhatsApp (`pengajuan:followup-wa`):**
   * **Jadwal:** Setiap **10 menit** (Timezone: `Asia/Jakarta`).
   * **Work Hours Guard:** Memeriksa status atasan via `ScheduleService::isUserWorkingNow($approver)`.
     * Atasan staf Normal: Hanya dikirimi pesan pada hari Senin s/d Jumat pada rentang jam dinas resmi.
     * Atasan staf Roster: Hanya dikirimi pesan jika sedang berada di dalam jam dinas shift aktif miliknya (Shift Pagi atau Shift Malam).
     * Saat atasan sedang di luar jam kerja atau sedang libur roster, pengiriman pesan otomatis ditunda demi kenyamanan istirahat karyawan.
   * **Anti-Spam Throttling:** Menggunakan kolom `last_notified_at` dengan batas jeda minimal 2 jam antar pengingat untuk mencegah banjir notifikasi.

---

*Manual arsitektur dan teknis ini merupakan dokumen resmi acuan pengembangan, integrasi, dan audit operasional ERP META Adhya Tirta Umbulan.*