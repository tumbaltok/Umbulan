# 📚 SYSTEM DOCUMENTATION & TECHNICAL MANUAL
## ERP META Adhya Tirta Umbulan

Dokumen ini berisi panduan teknis, arsitektur, alur kerja (*workflow*), serta rincian seluruh fitur yang ada pada Sistem ERP META Adhya Tirta Umbulan. Application framework yang digunakan adalah **Laravel 13** dengan runtime **PHP 8.3+**.

---

## 📑 Daftar Isi
1. [Arsitektur & Konsep Dasar](#-1-arsitektur--konsep-dasar)
2. [Sistem Hak Akses & Custom Role](#-2-sistem-hak-akses--custom-role)
3. [Modul Autentikasi & Keamanan Akun](#-3-modul-autentikasi--keamanan-akun)
4. [Modul Kehadiran & Penjadwalan Roster](#-4-modul-kehadiran--penjadwalan-roster)
5. [Modul Pengajuan Cuti & Izin](#-5-modul-pengajuan-cuti--izin)
6. [Modul Pengajuan CAR (Cash Advance Request)](#-6-modul-pengajuan-car-cash-advance-request)
7. [Modul Pengajuan MPR (Material Purchase Request)](#-7-modul-pengajuan-mpr-material-purchase-request)
8. [Modul Administrator & Record Data](#-8-modul-administrator--record-data)
9. [Otomatisasi System (Task Scheduler & Cron Job)](#-9-otomatisasi-system-task-scheduler--cron-job)
10. [Panduan Instalasi & Deployment Server](#-10-panduan-instalasi--deployment-server)

---

## 📐 1. Arsitektur & Konsep Dasar

Sistem ini dirancang untuk menangani operasional perusahaan air minum (PAMS) yang mencakup area kantor utama dan stasiun operasional lapangan 24/7.

* **Stack Teknologi**:
  * **Backend**: Laravel 13 Framework (PHP 8.3+).
  * **Database**: MySQL / MariaDB / SQLite.
  * **Frontend**: Blade Templating, Tailwind CSS, JavaScript Native.
  * **Eksternal API**: Gateway Fonnte (WhatsApp OTP & Notifications), Nager.Date API (Kalender Hari Libur Nasional).
  * **PDF Generator**: DomPDF.

---

## 🎭 2. Sistem Hak Akses & Custom Role

Untuk mengakomodasi dinamisnya struktur organisasi tanpa merusak logika aplikasi saat ada penambahan role baru, sistem menggunakan **Hirarki Level Role (`$roleLevel`)**:

| Level Role | Kelompok Role | Contoh Role | Wewenang & Alur Kerja |
| :---: | :--- | :--- | :--- |
| **Level 1** | System Administrator & Eksekutif | Admin, Direksi, General Manager | Full Access, Override Data, Persetujuan Final/Direct Approval |
| **Level 2** | Manajemen Menengah | Manager, Kepala Sektor | Persetujuan Final (Tahap 2) Pengajuan Cuti & CAR |
| **Level 3** | Pengawas Lapangan & HR | Supervisor, HRD, Humas | Persetujuan Awal (Tahap 1) Pengajuan Cuti, CAR, & MPR |
| **Level 4** | Staff Pelaksana | Staff, Operator Lapangan | Pemohon / Pengaju (*Requester*) |

---

## 🔐 3. Modul Autentikasi & Keamanan Akun

### A. Alur Registrasi Berjenjang
1. **Pendaftaran Web (`AuthController@registerWeb`)**:
   * User mendaftar dengan memilih **Sektor** (*Operasional* / *Manajemen*), **Stasiun Kerja**, dan **Jobdesk**.
   * System secara otomatis mengaitkan User ke **Supervisor (`supervisor_id`)** dan **Manager (`manager_id`)** terkait berdasarkan stasiun dan sektor.
2. **Verifikasi Dua Arah (2-FA)**:
   * **Email Link**: Verifikasi email standar Laravel (`MustVerifyEmail`).
   * **WhatsApp OTP**: Mengirimkan kode 6 angka melalui Fonnte API (`sendOtpPhone`).
3. **Perekaman Presensi Wajah**:
   * Mengambil sampel foto wajah melalui `JadwalController@registerFace` untuk menyimpan data visual (*descriptor*) sebagai verifikasi absensi.

---

## ⏰ 4. Modul Kehadiran & Penjadwalan Roster

Sistem menangani dua jenis jam kerja: **Normal** (5 hari kerja kantor) dan **Roster 24/7** (3 shift berputar untuk operasional stasiun).

### A. Rotasi Shift Roster Otomatis
* Rotasi shift operasional dipicu otomatis **Setiap Hari Selasa Pukul 07:00 WIB** menggunakan algoritma kalkulasi minggu pada `ScheduleService.php`.
* **Shift Pagi**: 07:00 – 15:00 WIB.
* **Shift Malam**: 23:00 – 07:00 WIB.

### B. Presensi Geofencing & Selfie (`KehadiranController`)

[ Buka Presensi ] ──> [ Hitung Jarak Geolocation ] ──> [ Ambil Foto Selfie ] ──> [ Validasi Radius ] ──> [ Simpan Presensi ]


1. **Perhitungan Radius Location**:
   * Menghitung posisi koordinat pengguna (`latitude`, `longitude`) terhadap koordinat Stasiun Kerja menggunakan **Formula Haversine**.
   * Jika posisi pengguna di luar radius stasiun, pengguna **wajib menyertakan alasan**.
2. **Perekaman Waktu & Foto**:
   * Sistem mencatat jam `check_in` dan `check_out` serta menyimpan berkas foto selfie ke `storage/app/public/foto_absensi/`.
   * Sistem otomatis membandingkan jam presensi dengan jadwal resmi untuk menentukan indikator **Terlambat** atau **Pulang Awal**.

---

## 🏖️ 5. Modul Pengajuan Cuti & Izin

Persetujuan Cuti menggunakan **Alur Sekuensial Bertingkat (Level 3 $\rightarrow$ Level 2/1)**.

### A. Alur Kerja Pengajuan Cuti
[ Karyawan Ajukan Cuti ] ──> [ Potong Saldo / Cek Kuota ] ──> [ Supervisor (L3) Approve ] ──> [ Manager/Admin (L1/L2) Approve ] ──> [ Auto-Sinkron Absensi ]


1. **Pengajuan & Penghitungan Hari Kerja**:
   * System menghitung jumlah hari kerja efektif (`hitungHariKerjaEfektif`) dengan mengecualikan Hari Libur Nasional secara real-time via *Nager.Date API*.
   * **Validasi Kuota**: Cuti Haid dibatasi maksimal 2 hari/bulan, Cuti Tahunan memotong `saldo_cutis`.
   * **Auto-Approval**: Pengajuan dari akun level Manajerial (Level 1 & 2) otomatis langsung `approved`.
2. **Persetujuan Berjenjang**:
   * **Tahap 1 (Supervisor - Level 3)**: Menyetujui $\rightarrow$ `status_supervisor = 'approved'`, status akhir tetap `pending`.
   * **Tahap 2 (Manager/Admin - Level 1 & 2)**: Menyetujui $\rightarrow$ `status_manager = 'approved'`, `status_akhir = 'approved'`.
3. **Auto-Inject Absensi & Pemotongan Saldo (`CutiHelperTrait`)**:
   * Ketika `status_akhir` bernilai `approved`, sistem memotong kuota saldo di database dan **otomatis menambahkan catatan Cuti di tabel `kehadirans`** pada rentang tanggal tersebut.

---

## 💸 6. Modul Pengajuan CAR (Cash Advance Request)

Modul ini digunakan untuk pengajuan uang muka/dana barang operasional dengan item jamak (*multi-item*).

### A. Alur Kerja Pengajuan CAR
[ Staff Buat CAR Multi-Item ] ──> [ SPV (L3) Approve Uang/Barang ] ──> [ Manager/Admin (L1/L2) Final Approve ] ──> [ Cetak PDF Form CAR ]


1. **Input Multi-Item**:
   * Karyawan dapat memasukkan beberapa rincian barang sekaligus (nama barang, jumlah, estimasi harga per item, dan lampiran nota/proposal).
2. **Approval Hirarki**:
   * **Supervisor (Level 3)**: Memeriksa kebutuhan fisik di lapangan.
   * **Manager / Admin (Level 1 & 2)**: Menyetujui porsi pencairan anggaran.
3. **Pencetakan Form Resmi (`carcetak.blade.php`)**:
   * Setelah `status_manager = 'approved'`, form CAR berformat PDF resmi siap dicetak / diunduh.

---

## 📦 7. Modul Pengajuan MPR (Material Purchase Request)

Didesain khusus untuk pengadaan material darurat / barang operasional stasiun dengan **Alur Bypass / Direct Parallel Approval**.

### A. Alur Kerja Pengajuan MPR
[ Karyawan Ajukan MPR ] ──> [ Kirim Blast Notifikasi WA ke Semua Atasan ]
│
┌────────────────────────┴────────────────────────┐
▼                                                 ▼
[ SPV (L3) Langsung Setujui ]                     [ Manager/Admin (L1/L2) Langsung Setujui ]
│                                                 │
└────────────────────────┬────────────────────────┘
▼
[ Status Akhir LANGSUNG Approved ]


1. **Direct Notification (WhatsApp)**:
   * Saat MPR dibuat, sistem langsung memicu *broadcast notification* via WhatsApp Fonnte API ke seluruh atasan di stasiun kerja terkait.
2. **Direct Approval (Bypass)**:
   * Pengajuan MPR **langsung masuk ke antrean semua atasan (Level 1, 2, dan 3) secara bersamaan**.
   * Siapa pun atasan yang melakukan *action* (Approve/Reject) lebih dahulu, `status_akhir` **LANGSUNG berubah menjadi `approved` / `rejected`** tanpa perlu menunggu persetujuan berjenjang.
3. **Cetak Dokumen (`mprcetak.blade.php`)**:
   * Dokumen cetak PDF tergenerasi lengkap dengan tanda tangan digital atasan yang menyetujui.

---

## 🛠️ 8. Modul Administrator & Record Data

Modul khusus ini diakses oleh User dengan Role Level 1 & Level 2 untuk melakukan pengawasan operasional (*monitoring*):

1. **Manajemen Karyawan (`KaryawanController`)**:
   * Melihat daftar seluruh karyawan, status aktif/cuti hari ini, detail akun, serta kemampuan mengubah sisa saldo cuti secara independen.
2. **Manajemen Stasiun Kerja (`StationController`)**:
   * Mengatur master data 22 stasiun/lokasi kerja (4 Stasiun Utama + 18 Rumah Meter), pemetaan koordinat lat-long, dan penyetelan radius geofencing.
3. **Manajemen Role & Jobdesk (`RoleController`)**:
   * Mengatur hirarki jabatan (`parent_role_id`), divisi, level role, serta kategori tugas pekerjaan.
4. **Export Record Data (`RecordController`)**:
   * Rekapitulasi dan ekspor data histori **Cuti**, **CAR**, dan **MPR** ke format file spreadsheet/CSV berdasarkan filter bulan dan tahun.

---

## 🤖 9. Otomatisasi System (Task Scheduler & Cron Job)

Sistem menjalankan otomatisasi latar belakang melalui Cron Job Laravel (`routes/console.php`):

```bash
* * * * * cd /var/www/nama-proyek && php artisan schedule:run >> /dev/null 2>&1
Reset Saldo Haid Bulanan (saldo:reset-haid):

Dijalankan otomatis Setiap tanggal 1 pukul 00:00 WIB untuk mereset kuota cuti haid karyawan wanita menjadi 2 hari per bulan.

Reset Saldo Cuti Tahunan (saldo:reset-tahunan):

Dijalankan otomatis Setiap tanggal 1 Januari pukul 00:00 WIB untuk membuat jatah saldo cuti tahunan baru (12 hari) bagi seluruh karyawan.

Scheduler Follow-Up WA (Setiap 10 Menit):

Berjalan otomatis di jam kerja untuk memindai pengajuan yang masih berstatus pending dan mengirimkan pengingat pesan WhatsApp ke atasan terkait.

🚀 10. Panduan Instalasi & Deployment Server
Langkah-Langkah Deployment di VPS Linux (Ubuntu/Nginx/Apache)
Clone Repository:

Bash
cd /var/www
git clone <URL_REPOSITORY_GITHUB> meta-erp
cd meta-erp
Install Dependencies (Composer):

Bash
composer install --no-dev --optimize-autoloader
Konfigurasi Environment (.env):

Bash
cp .env.example .env
nano .env
Atur variabel penting seperti DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL, dan FONNTE_TOKEN.

Generate App Key & Migration:

Bash
php artisan key:generate
php artisan migrate --force


Symlink Storage & Set Permission:

Bash
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache


Optimasi Cache:

Bash
php artisan optimize
Manual teknis ini menjadi acuan baku dalam pengoperasian, pemeliharaan, serta pengembangan fitur ERP META Adhya Tirta Umbulan.