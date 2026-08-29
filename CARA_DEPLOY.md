# 🚀 Panduan Deployment Server Produksi Bare-Metal / VPS
## ERP META Adhya Tirta Umbulan (Water Transmission Enterprise System)

Dokumen ini merupakan panduan teknis resmi implementasi dan deployment proyek **ERP META Adhya Tirta Umbulan** dari server kosong (*bare-metal* / VPS / Cloud Server berbasis Ubuntu 22.04 LTS atau Ubuntu 24.04 LTS) hingga berjalan penuh, aman, stabil, terisolasi, dan teroptimasi secara online menggunakan domain publik bersertifikat SSL HTTPS.

> [!IMPORTANT]
> **Kebijakan Keamanan Kredensial Produksi (Zero Sensitive Data Leak):**
> Seluruh sintaks dan konfigurasi dalam panduan ini secara ketat menggunakan format placeholder yang aman (seperti `domain-anda.com`, `nama_database_anda`, dan `password_database_kuat`). Dilarang keras melakukan commit atau menyebarkan kredensial asli produksi, kunci enkripsi aplikasi (`APP_KEY`), maupun password database ke repositori publik.

---

## 📑 Daftar Isi
1. [Spesifikasi Server & Diagram Topologi Jaringan](#1-spesifikasi-server--diagram-topologi-jaringan)
2. [Langkah 1: Persiapan Server & Konfigurasi Firewall UFW](#langkah-1-persiapan-server--konfigurasi-firewall-ufw)
3. [Langkah 2: Instalasi Paket Inti Server & PHP 8.3 Ekstensi](#langkah-2-instalasi-paket-inti-server--php-83-ekstensi)
4. [Langkah 3: Instalasi Composer 2, Node.js LTS, dan PM2](#langkah-3-instalasi-composer-2-nodejs-lts-dan-pm2)
5. [Langkah 4: Setup Database Server (MySQL / MariaDB)](#langkah-4-setup-database-server-mysql--mariadb)
6. [Langkah 5: Kloning Repositori, Konfigurasi `.env`, dan Generate Key](#langkah-5-kloning-repositori-konfigurasi-env-dan-generate-key)
7. [Langkah 6: Instalasi Dependensi PHP & Kompilasi Asset Frontend](#langkah-6-instalasi-dependensi-php--kompilasi-asset-frontend)
8. [Langkah 7: Setup & Daemonize WhatsApp Baileys Microservice](#langkah-7-setup--daemonize-whatsapp-baileys-microservice)
9. [Langkah 8: Migrasi Database & Pembuatan Storage Symlink](#langkah-8-migrasi-database--pembuatan-storage-symlink)
10. [Langkah 9: Pengaturan Hak Akses Direktori (Permissions & Ownership)](#langkah-9-pengaturan-hak-akses-direktori-permissions--ownership)
11. [Langkah 10: Konfigurasi Background Services (Supervisor & Crontab)](#langkah-10-konfigurasi-background-services-supervisor--crontab)
12. [Langkah 11: Konfigurasi Web Server Nginx & SSL Let's Encrypt](#langkah-11-konfigurasi-web-server-nginx--ssl-lets-encrypt)
13. [Langkah 12: Optimasi Cache & Performa Produksi](#langkah-12-optimasi-cache--performa-produksi)
14. [Prosedur Pemeliharaan & Skrip Update Rutin (`deploy.sh`)](#prosedur-pemeliharaan--skrip-update-rutin-deploysh)
15. [Checklist Pengujian Pasca-Deploy (Go-Live Verification)](#checklist-pengujian-pasca-deploy-go-live-verification)

---

## 1. Spesifikasi Server & Diagram Topologi Jaringan

### A. Diagram Topologi Arsitektur Produksi
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

### B. Spesifikasi Minimum & Rekomendasi Hardware Server:
* **Sistem Operasi:** Ubuntu 22.04 LTS (*Jammy Jellyfish*) atau Ubuntu 24.04 LTS (*Noble Numbat*) 64-bit.
* **Processor (CPU):** Minimal 2 vCPU (Disarankan 4 vCPU agar proses kompilasi asset Vite dan enkripsi WebSocket Baileys tidak mengalami lonjakan beban CPU).
* **Memori RAM:** Minimal 2 GB RAM + 2 GB Swap (Disarankan 4 GB RAM tanpa swap agar tidak terjadi *Out-of-Memory* / OOM Killer saat menjalankan build asset dan background worker bersamaan).
* **Ruang Penyimpanan (Storage):** Minimal 25 GB SSD / NVMe (untuk menampung berkas aplikasi, lampiran nota CAR/MPR, berkas foto bukti presensi berstempel, dan riwayat log rotasi harian).
* **Jaringan Jaringan (Networking):** Alamat IP Publik Statis (*Static Public IPv4*) dengan Domain aktif yang sudah diarahkan (*DNS A Record*).

---

## Langkah 1: Persiapan Server & Konfigurasi Firewall UFW

Lakukan koneksi ke server melalui terminal SSH:

```bash
ssh username@ip-server-anda
```

### 1.1. Perbarui Indeks Repositori & Paket Sistem
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip zip software-properties-common ca-certificates lsb-release gnupg ufw
```
* **Tujuan / Kegunaan:** Memperbarui seluruh kernel dan pustaka keamanan OS Ubuntu ke versi terbaru serta memasang alat utilitas dasar jaringan dan kompresi file.
* **Dampak Jika Terlewat:** Server rentan terhadap celah keamanan lawas (*zero-day exploits*), dan perintah manipulasi arsip (`unzip`, `curl`) akan gagal dieksekusi oleh Composer.

---

### 1.2. Amankan Port Jaringan Menggunakan UFW (Uncomplicated Firewall)
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

## Langkah 2: Instalasi Paket Inti Server & PHP 8.3 Ekstensi

Framework Laravel pada sistem ini membutuhkan PHP versi 8.3 dengan ekstensi-ekstensi spesifik untuk pengolahan gambar, basis data, dan manipulasi teks.

### 2.1. Tambahkan Repository Resmi PPA Ondřej Surý
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```
* **Tujuan / Kegunaan:** Menyediakan paket biner resmi PHP versi 8.3 yang mutakhir untuk sistem operasi Ubuntu.
* **Dampak Jika Terlewat:** Repository bawaan Ubuntu 22.04 hanya menyediakan PHP 8.1 yang tidak didukung oleh framework Laravel terbaru (memerlukan `php: ^8.3`).

---

### 2.2. Pasang PHP 8.3-FPM Beserta Seluruh Ekstensi Wajib
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

## Langkah 3: Instalasi Composer 2, Node.js LTS, dan PM2

### 3.1. Pasang Composer 2 (PHP Dependency Manager)
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
composer --version
```
* **Tujuan / Kegunaan:** Mengunduh dan memasang manajer paket dependensi PHP resmi secara global pada sistem operasi.
* **Dampak Jika Terlewat:** Server tidak dapat mengunduh pustaka vendor Laravel (`vendor/`).

---

### 3.2. Pasang Node.js 20/22 LTS & PM2 (Process Manager)
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
* **Tujuan / Kegunaan:** Memasang runtime JavaScript engine dan PM2 untuk menjaga proses Node.js Baileys tetap aktif terus-menerus (*daemonized*).
* **Dampak Jika Terlewat:** Server tidak dapat melakukan kompilasi CSS frontend, dan microservice WhatsApp tidak dapat dijalankan di latar belakang.

---

## Langkah 4: Setup Database Server (MySQL / MariaDB)

### 4.1. Instalasi MySQL Server
```bash
sudo apt install -y mysql-server
sudo systemctl enable --now mysql
```

Jalankan skrip pengamanan instalasi MySQL:
```bash
sudo mysql_secure_installation
```
*(Pilih opsi `Y` untuk menghapus akun anonim, menonaktifkan login root jarak jauh, dan menghapus database test).*

---

### 4.2. Buat Database & User Khusus ERP Umbulan
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
* **Tujuan / Kegunaan:** Membuat basis data ber-charset `utf8mb4` (mendukung penuh karakter multibyte dan emoji WhatsApp) serta membatasi login user hanya dari host lokal `127.0.0.1`.
* **Dampak Jika Terlewat:** Menggunakan akun `root` tanpa batasan host melanggar prinsip *Least Privilege* dan membahayakan seluruh server jika kredensial aplikasi bocor.

---

## Langkah 5: Kloning Repositori, Konfigurasi `.env`, dan Generate Key

Direktori baku penempatan aplikasi web di sistem operasi Linux Ubuntu adalah `/var/www/`.

### 5.1. Kloning Repositori Proyek
```bash
cd /var/www
sudo git clone https://github.com/username-anda/repository-anda.git umbulan
cd /var/www/umbulan
```

---

### 5.2. Konfigurasi File Lingkungan Produksi (`.env`)
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

---

### 5.3. Generate Application Key (Kunci Enkripsi)
```bash
php artisan key:generate --force
```
* **Tujuan / Kegunaan:** Menghasilkan 32-karakter kunci acak base64 ke dalam variabel `APP_KEY` pada file `.env` untuk mengenkripsi sesi login, cookie, token otorisasi, dan password hash pengguna.
* **Dampak Jika Terlewat:** Aplikasi menolak melayani request dengan error fatal: *No application encryption key has been specified*.

---

## Langkah 6: Instalasi Dependensi PHP & Kompilasi Asset Frontend

### 6.1. Instalasi Dependensi PHP (Composer Production)
```bash
cd /var/www/umbulan
composer install --no-dev --optimize-autoloader --no-interaction
```
* **Tujuan / Kegunaan:** Mengunduh seluruh pustaka framework Laravel dan dependensi produksi, mengabaikan pustaka testing/debugging (`--no-dev`), dan menyusun *classmap* teroptimasi (`--optimize-autoloader`) untuk memangkas waktu pemuatan file PHP hingga 30%.
* **Dampak Jika Terlewat:** Folder `vendor/` tidak tersedia, menyebabkan *autoloading failure* seketika.

---

### 6.2. Instalasi Dependensi Frontend & Kompilasi Vite (Tailwind CSS v4)
```bash
npm ci
npm run build
```
* **Tujuan / Kegunaan:** `npm ci` mengunduh paket dependensi Node.js sesuai penguncian `package-lock.json`. `npm run build` memicu compiler Vite untuk memproses berkas CSS Tailwind v4 (`@tailwindcss/vite`), font Instrument Sans, JavaScript Turbo Drive, dan menghasilkan bundel produksi pada direktori `public/build/`.
* **Dampak Jika Terlewat:** Seluruh tampilan antarmuka aplikasi di peramban akan rusak parah tanpa format CSS (*raw unstyled HTML*), modal presensi tidak bisa dirender, dan antarmuka biometrik wajah lumpuh.

---

## Langkah 7: Setup & Daemonize WhatsApp Baileys Microservice

Microservice WhatsApp Gateway terletak di subfolder `whatsapp-service/` dan berjalan sebagai service Node.js mandiri berbasis soket WebSocket multidevice.

### 7.1. Instalasi Dependensi Microservice
```bash
cd /var/www/umbulan/whatsapp-service
npm ci
```
* **Tujuan / Kegunaan:** Memasang modul `@whiskeysockets/baileys`, `express`, `qrcode`, dan pustaka pendukung lainnya di dalam folder `whatsapp-service/node_modules`.
* **Dampak Jika Terlewat:** Microservice gagal dijalankan dengan pesan error: *Cannot find package '@whiskeysockets/baileys'*.

---

### 7.2. Jalankan Microservice Menggunakan PM2 di Bawah User `www-data`
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
* **Tujuan / Kegunaan:** Menjalankan microservice WhatsApp secara persisten sebagai daemon latar belakang di bawah identitas user `www-data`. Jika proses mengalami *crash* atau server di-*restart*, PM2 akan secara otomatis menghidupkan kembali service dalam hitungan detik.
* **Dampak Jika Terlewat:** Jika microservice dijalankan langsung via `node server.js` di terminal SSH, proses akan mati seketika saat sesi SSH ditutup, menyebabkan seluruh notifikasi OTP dan approval WhatsApp terhenti total. Menjalankannya di bawah user `root` juga berbahaya karena folder sesi `auth_session/` tidak akan bisa diakses oleh controller Laravel.

---

### 7.3. Uji Endpoint Internal Microservice
Verifikasi bahwa microservice telah mendengarkan request pada port lokal `3001`:
```bash
curl http://127.0.0.1:3001/status
```
* **Output yang Diharapkan:**
  `{"success":true,"online":true,"status":"disconnected","phone":null,"qr":null,"uptime":...}`

---

## Langkah 8: Migrasi Database & Pembuatan Storage Symlink

Kembali ke direktori utama aplikasi:
```bash
cd /var/www/umbulan
```

### 8.1. Eksekusi Migrasi Database & Seeding Data Baku
```bash
php artisan migrate --force --seed
```
* **Tujuan / Kegunaan:** Membuat seluruh skema tabel basis data (pengguna, kehadiran, stasiun, cuti, car, mpr, sesi), serta menginjeksi data master awal (*seeders*) seperti role jabatan, akun administrator default, stasiun kerja, dan jenis cuti resmi. Parameter `--force` wajib diberikan untuk mengonfirmasi operasi pada mode `APP_ENV=production`.
* **Dampak Jika Terlewat:** Basis data kosong, login ditolak, dan seluruh rute query melempar *Table not found error*.

---

### 8.2. Buat Symlink Storage Publik
```bash
php artisan storage:link
```
* **Tujuan / Kegunaan:** Membuat tautan simbolis (*symbolic link*) dari direktori `storage/app/public` menuju `public/storage`.
* **Dampak Jika Terlewat:** File yang diunggah pengguna (foto profil, tanda tangan digital, foto stempel watermark presensi, lampiran nota CAR, dan berkas teknis MPR) akan tersimpan di disk namun **gagal ditampilkan di peramban web (*HTTP 404 Not Found*)**.

---

## Langkah 9: Pengaturan Hak Akses Direktori (Permissions & Ownership)

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

* **Tujuan / Kegunaan:** Memberikan hak baca dan eksekusi pada web server untuk seluruh berkas, serta hak tulis eksklusif bagi framework pada folder `storage` (log dan upload), `bootstrap/cache` (optimasi konfigurasi), dan `whatsapp-service/auth_session` (kunci enkripsi sesi Baileys).
* **Dampak Jika Terlewat:**
  * Jika `storage` tidak writable $\rightarrow$ Laravel melempar *The stream or file ".../laravel.log" could not be opened in append mode: Failed to open stream: Permission denied*.
  * Jika `auth_session` tidak writable $\rightarrow$ Microservice WhatsApp melempar error *EACCES: permission denied*, QR Code tidak bisa dibuat, dan sesi koneksi selalu terputus.

---

## Langkah 10: Konfigurasi Background Services (Supervisor & Crontab)

Aplikasi memiliki dua pekerjaan latar belakang independen:
1. **Queue Worker:** Memproses antrean asinkron pengiriman email OTP pemulihan kata sandi.
2. **Task Scheduler:** Memproses reset saldo cuti dan pengingat WhatsApp secara terjadwal.

---

### 10.1. Konfigurasi Queue Worker Menggunakan Supervisor
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
* **Tujuan / Kegunaan:** Menjalankan 2 proses worker paralel (`numprocs=2`) di bawah user `www-data` yang membaca tabel antrean `jobs`.
* **Dampak Jika Terlewat:** Email verifikasi OTP atau notifikasi background tidak akan pernah dikirimkan ke kotak masuk pengguna karena tertahan selamanya di antrean database.

---

### 10.2. Konfigurasi Otomatisasi Task Scheduler Menggunakan Crontab
Buka crontab milik user sistem `www-data`:
```bash
sudo crontab -u www-data -e
```

Sisipkan baris perintah berikut pada baris paling bawah:
```cron
* * * * * cd /var/www/umbulan && php artisan schedule:run >> /dev/null 2>&1
```

* **Tujuan / Kegunaan:** Memicu Task Scheduler Laravel setiap menit. Sesuai definisi [routes/console.php](file:///c:/laragon/www/Umbulan/routes/console.php), cron ini mengeksekusi secara otomatis:
  1. `saldo:reset-haid`: Reset kuota 2 hari Cuti Haid setiap tanggal 1 awal bulan pukul 00:00 WIB.
  2. `saldo:reset-tahunan`: Reset 12 hari Cuti Tahunan setiap tanggal 1 Januari pukul 00:00 WIB.
  3. `pengajuan:followup-wa`: Mengirim pengingat WhatsApp setiap 10 menit dengan validasi jam dinas aktif atasan (*Work Hours Guard*).
* **Dampak Jika Terlewat:** Fitur automasi cuti dan pengingat approval WhatsApp macet total, memaksa pengurus sistem melakukan reset manual lewat database.

---

## Langkah 11: Konfigurasi Web Server Nginx & SSL Let's Encrypt

### 11.1. Pasang Nginx & Certbot
```bash
sudo apt install -y nginx certbot python3-certbot-nginx
```

---

### 11.2. Buat Konfigurasi Virtual Host Nginx
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

    location ~* /(composer\.(json|lock)|package(-lock)?\.json|\.env.*|CARA_DEPLOY\.md|SYSTEM_DOCUMENTATION\.md)$ {
        deny all;
    }
}
```

---

### 11.3. Aktifkan Site & Uji Sintaks Konfigurasi Nginx
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

---

### 11.4. Pasang Sertifikat SSL Gratis (Certbot Let's Encrypt)
Pastikan DNS A Record domain Anda (`domain-anda.com`) telah mengarah ke alamat IP server publik, lalu jalankan:

```bash
sudo certbot --nginx -d domain-anda.com
```
* Masukkan alamat email untuk notifikasi kedaluwarsa sertifikat.
* Setujui persyaratan layanan (*Terms of Service*).
* Certbot akan secara otomatis menginjeksikan konfigurasi SSL ke dalam berkas `umbulan.conf` dan mendaftarkan pembaruan otomatis (*cron auto-renewal*).

---

## Langkah 12: Optimasi Cache & Performa Produksi

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

* **Tujuan / Kegunaan:** Menggabungkan seluruh file di folder `config/`, seluruh definisi di `routes/web.php`, dan template Blade menjadi file *bytecode* PHP tunggal yang disimpan di RAM/cache.
* **Dampak Jika Terlewat:** Waktu respons aplikasi menjadi lebih lambat karena framework harus memindai puluhan file konfigurasi secara berulang pada setiap siklus HTTP request.

---

## Prosedur Pemeliharaan & Skrip Update Rutin (`deploy.sh`)

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

### Cara Eksekusi Update Rutin di Masa Mendatang:
Kapan pun ada pembaruan kode baru dari repositori GitHub, Anda cukup login ke server dan mengeksekusi tepat satu baris perintah berikut:
```bash
cd /var/www/umbulan && sudo ./deploy.sh
```

---

## Checklist Pengujian Pasca-Deploy (Go-Live Verification)

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

*Panduan deployment teknis ini merupakan dokumen resmi operasional server ERP META Adhya Tirta Umbulan.*
