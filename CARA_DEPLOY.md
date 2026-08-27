# Panduan Deployment Server Produksi
## ERP META Adhya Tirta Umbulan (Water Transmission ERP)

Dokumen ini merupakan panduan komprehensif implementasi dan deployment teknis proyek **ERP META Adhya Tirta Umbulan** dari server kosong (*bare metal* / VPS / Cloud Server) hingga beroperasi secara penuh, stabil, aman, dan teroptimasi pada lingkungan produksi (*production*).

> [!IMPORTANT]
> **Kebijakan Keamanan Kredensial (Zero Sensitive Data Leak)**:
> Dokumen ini secara ketat menggunakan format placeholder aman (seperti `domain-anda.com`, `nama_database_anda`, `password_database_anda_yang_kuat`). Dilarang keras melakukan commit atau menyebarkan kredensial asli produksi, kunci enkripsi, maupun token otentikasi ke dalam repositori publik.

---

## 📑 Daftar Isi
1. [Prasyarat Server & Spesifikasi Minimum](#1-prasyarat-server--spesifikasi-minimum)
2. [Langkah 1: Persiapan Server & Instalasi Dependensi Inti](#langkah-1-persiapan-server--instalasi-dependensi-inti)
3. [Langkah 2: Clone Repository & Pemasangan Pustaka](#langkah-2-clone-repository--pemasangan-pustaka)
4. [Langkah 3: Konfigurasi Environment Produksi (.env)](#langkah-3-konfigurasi-environment-produksi-env)
5. [Langkah 4: Setup Database, Enkripsi, & Storage Link](#langkah-4-setup-database-enkripsi--storage-link)
6. [Langkah 5: Konfigurasi Hak Akses Direktori (Permissions)](#langkah-5-konfigurasi-hak-akses-direktori-permissions)
7. [Langkah 6: Daemonizing Background Services (PM2 & Supervisor)](#langkah-6-daemonizing-background-services-pm2--supervisor)
8. [Langkah 7: Otomatisasi Task Scheduler & Cron Job](#langkah-7-otomatisasi-task-scheduler--cron-job)
9. [Langkah 8: Konfigurasi Web Server Nginx & SSL HTTPS](#langkah-8-konfigurasi-web-server-nginx--ssl-https)
10. [Langkah 9: Caching & Optimasi Produksi](#langkah-9-caching--optimasi-produksi)
11. [Langkah 10: Prosedur Pemeliharaan & Update Aplikasi (CI/CD Script)](#langkah-10-prosedur-pemeliharaan--update-aplikasi-cicd-script)
12. [Checklist Akhir Pasca-Deploy (Go-Live Verification)](#checklist-akhir-pasca-deploy-go-live-verification)

---

## 1. Prasyarat Server & Spesifikasi Minimum

### A. Diagram Arsitektur Deployment
```text
                    [ Klien / Browser Web ]
                              │
                    HTTPS (Port 443) / SSL
                              │
                     [ Nginx Web Server ]
                              │
              ┌───────────────┴───────────────┐
              │                               │
    [ PHP 8.3-FPM ]                   [ Node.js Baileys ]
   (Laravel 11/12 ERP)             (WhatsApp Gateway Microservice)
      Port: FastCGI                     Port: 127.0.0.1:3001
              │                               │
              ├────────── MySQL 8.0+ ─────────┤
              │     (Database Relasional)     │
              │                               │
   [ Laravel Task Scheduler ]        [ Persistent Socket ]
   (Cron: Haid, Tahunan, WA)       (WhatsApp Web Multidevice)
```

### B. Spesifikasi Minimum Server
* **Sistem Operasi:** Ubuntu 22.04 LTS atau Ubuntu 24.04 LTS (x86_64).
* **Processor (CPU):** Minimal 2 vCPU (Disarankan 4 vCPU untuk kompilasi dan pemrosesan paralel).
* **Memori (RAM):** Minimal 2 GB (Disarankan 4 GB agar kompilasi Vite dan Baileys WhatsApp berjalan tanpa *out-of-memory*).
* **Ruang Penyimpanan (Disk):** Minimal 20 GB SSD / NVMe.
* **Firewall / Port Jaringan:** Port 22 (SSH), Port 80 (HTTP), Port 443 (HTTPS). Port 3001 (WhatsApp Gateway) **wajib tertutup dari publik** (hanya diakses internal `127.0.0.1`).

---

## Langkah 1: Persiapan Server & Instalasi Dependensi Inti

Login ke server menggunakan SSH sebagai user `root` atau user dengan hak akses `sudo`:

```bash
ssh username@ip-server-anda
```

### 1.1. Pembaruan Paket Sistem
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip zip software-properties-common ca-certificates lsb-release gnupg ufw supervisor
```

### 1.2. Instalasi PHP 8.3 & Ekstensi yang Dibutuhkan
Tambahkan repository PPA resmi Ondřej Surý untuk mendapatkan PHP 8.3:

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath \
    php8.3-curl php8.3-gd php8.3-zip php8.3-intl php8.3-sqlite3
```

Verifikasi instalasi PHP:
```bash
php -v
# Output: PHP 8.3.x (cli) ...
```

### 1.3. Instalasi Composer 2 (PHP Dependency Manager)
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

composer --version
# Output: Composer version 2.x.x ...
```

### 1.4. Instalasi Node.js 20 LTS & PM2 (Process Manager)
Aplikasi membutuhkan Node.js untuk kompilasi frontend Vite (Tailwind CSS v4) dan runtime microservice WhatsApp Baileys:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Pasang PM2 secara global untuk manajemen background service WhatsApp
sudo npm install -g pm2

node -v    # Output: v20.x.x
npm -v     # Output: 10.x.x
pm2 -v     # Output: 5.x.x
```

### 1.5. Instalasi & Konfigurasi Database Server (MySQL)
```bash
sudo apt install -y mysql-server

# Jalankan pengamanan instalasi database
sudo mysql_secure_installation
```

Masuk ke konsol MySQL untuk membuat database dan user khusus ERP Umbulan:
```bash
sudo mysql -u root -p
```

Eksekusi kueri SQL berikut di dalam prompt MySQL (gunakan kredensial kuat Anda sendiri):
```sql
CREATE DATABASE nama_database_anda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nama_user_database'@'127.0.0.1' IDENTIFIED BY 'password_database_anda_yang_kuat';
GRANT ALL PRIVILEGES ON nama_database_anda.* TO 'nama_user_database'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

### 1.6. Instalasi Web Server Nginx & Certbot (SSL Let's Encrypt)
```bash
sudo apt install -y nginx certbot python3-certbot-nginx
```

---

## Langkah 2: Clone Repository & Pemasangan Pustaka

Direktori standar penempatan aplikasi web di Ubuntu adalah `/var/www/`.

### 2.1. Clone Repository Proyek
```bash
cd /var/www
sudo git clone https://github.com/username-anda/repository-anda.git umbulan
cd /var/www/umbulan
```

### 2.2. Instalasi Dependensi PHP (Composer)
Jalankan instalasi dependensi tanpa pustaka testing/development:
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

### 2.3. Instalasi Dependensi Frontend & Kompilasi Asset (Vite)
```bash
npm ci
npm run build
```
> **Hasil Kompilasi:** Perintah ini akan menghasilkan direktori `public/build/` yang berisi file CSS (Tailwind v4) dan JS produksi.

### 2.4. Instalasi Dependensi Microservice WhatsApp Baileys
Masuk ke folder `whatsapp-service/` dan pasang dependensi Node.js:
```bash
cd /var/www/umbulan/whatsapp-service
npm ci
cd /var/www/umbulan
```

---

## Langkah 3: Konfigurasi Environment Produksi (.env)

Salin template environment dan sesuaikan konfigurasinya:
```bash
cd /var/www/umbulan
cp .env.example .env
nano .env
```

Sesuaikan variabel-variabel kunci berikut untuk produksi (gunakan format placeholder aman berikut):

```dotenv
# ===================================================
# KONFIGURASI APLIKASI
# ===================================================
APP_NAME="ERP META Adhya Tirta Umbulan"
APP_ENV=production
APP_KEY=base64:GENERATE_VIA_ARTISAN
APP_DEBUG=false
APP_URL=https://domain-anda.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Asia/Jakarta

# ===================================================
# LOGGING
# ===================================================
LOG_CHANNEL=daily
LOG_LEVEL=info

# ===================================================
# DATABASE UTAMA (MySQL)
# ===================================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=nama_user_database
DB_PASSWORD=password_database_anda_yang_kuat

# ===================================================
# SESSION, CACHE & QUEUE
# ===================================================
SESSION_DRIVER=database
SESSION_LIFETIME=43200
SESSION_ENCRYPT=false

CACHE_STORE=database
QUEUE_CONNECTION=database

# ===================================================
# FILESYSTEM STORAGE
# ===================================================
FILESYSTEM_DISK=public

# ===================================================
# WHATSAPP GATEWAY MICROSERVICE (BAILEYS)
# ===================================================
WHATSAPP_SERVICE_URL=http://127.0.0.1:3001

# ===================================================
# EMAIL (SMTP / Notifikasi)
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

Simpan file dengan menekan `Ctrl + O`, lalu `Enter`, kemudian keluar dengan `Ctrl + X`.

---

## Langkah 4: Setup Database, Enkripsi, & Storage Link

Eksekusi perintah Artisan untuk inisialisasi aplikasi:

```bash
cd /var/www/umbulan

# 1. Generate Application Encryption Key
php artisan key:generate --force

# 2. Migrasi Database & Seeding Data Awal
# Menginisialisasi tabel skema, role, stasiun, jenis cuti, & akun default
php artisan migrate --force --seed

# 3. Buat Symlink Storage Publik
# Menghubungkan storage/app/public ke public/storage untuk foto profil, ttd, dan bukti absen
php artisan storage:link
```

---

## Langkah 5: Konfigurasi Hak Akses Direktori (Permissions)

Web server Nginx dan PHP-FPM di Ubuntu berjalan di bawah user dan grup `www-data`. Berikan hak akses kepemilikan yang tepat agar sistem dapat mengunggah file dan menyimpan sesi WhatsApp:

```bash
cd /var/www/umbulan

# 1. Pastikan direktori sesi WhatsApp sudah ada
mkdir -p whatsapp-service/auth_session

# 2. Atur kepemilikan seluruh folder ke user web server (www-data)
sudo chown -R www-data:www-data /var/www/umbulan

# 3. Set permission direktori (755) dan file (644)
sudo find /var/www/umbulan -type d -exec chmod 755 {} \;
sudo find /var/www/umbulan -type f -exec chmod 644 {} \;

# 4. Berikan izin tulis penuh pada folder storage, cache, dan sesi WhatsApp
sudo chmod -R 775 /var/www/umbulan/storage
sudo chmod -R 775 /var/www/umbulan/bootstrap/cache
sudo chmod -R 775 /var/www/umbulan/whatsapp-service/auth_session
```

---

## Langkah 6: Daemonizing Background Services (PM2 & Supervisor)

Terdapat 2 layanan latar belakang yang harus tetap hidup 24/7 dan otomatis menyala kembali jika server mengalami reboot:
1. **WhatsApp Gateway Microservice (`whatsapp-service/server.js`)**
2. **Laravel Queue Worker (`php artisan queue:work`)**

---

### 6.1. Menjalankan WhatsApp Microservice dengan PM2

1. Masuk ke direktori `whatsapp-service`:
   ```bash
   cd /var/www/umbulan/whatsapp-service
   ```

2. Jalankan server Baileys di bawah user `www-data`:
   ```bash
   sudo -u www-data pm2 start server.js --name "umbulan-whatsapp" --time
   ```

3. Simpan daftar proses PM2 dan aktifkan auto-start saat reboot:
   ```bash
   sudo -u www-data pm2 save
   
   # Konfigurasi startup systemd
   sudo pm2 startup systemd -u www-data --hp /var/www
   ```

4. Periksa status microservice:
   ```bash
   sudo -u www-data pm2 status
   # Status harus "online"
   ```

5. Verifikasi bahwa endpoint internal WhatsApp gateway merespons:
   ```bash
   curl http://127.0.0.1:3001/status
   # Mengembalikan JSON: {"online":true,"status":"...","phone":...}
   ```

---

### 6.2. Menjalankan Laravel Queue Worker dengan Supervisor

Aplikasi menggunakan `QUEUE_CONNECTION=database` untuk memproses tugas-tugas asinkron di latar belakang.

1. Buat file konfigurasi Supervisor:
   ```bash
   sudo nano /etc/supervisor/conf.d/umbulan-worker.conf
   ```

2. Masukkan konfigurasi berikut:
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

3. Muat dan jalankan konfigurasi Supervisor:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start umbulan-worker:*
   ```

4. Cek status worker:
   ```bash
   sudo supervisorctl status
   # Output harus:
   # umbulan-worker:umbulan-worker_00 RUNNING
   # umbulan-worker:umbulan-worker_01 RUNNING
   ```

---

## Langkah 7: Otomatisasi Task Scheduler & Cron Job

Laravel Task Scheduler bertugas menjalankan automasi bisnis ERP secara terjadwal.

### 7.1. Konfigurasi Crontab Server
Buka crontab milik user `www-data`:
```bash
sudo crontab -u www-data -e
```

Tambahkan baris berikut di baris paling bawah:
```cron
* * * * * cd /var/www/umbulan && php artisan schedule:run >> /dev/null 2>&1
```

---

### 7.2. Rincian Task Otomatis yang Berjalan
Berdasarkan `routes/console.php`, sistem secara otomatis mengeksekusi 3 tugas rutin:

| Nama Perintah | Jadwal Eksekusi | Deskripsi & Aturan Bisnis |
| :--- | :--- | :--- |
| **`saldo:reset-haid`** | Setiap tanggal 1 awal bulan, pukul **00:00 WIB** | Mengosongkan pemakaian cuti haid bulan sebelumnya dan mereset kuota Cuti Haid (maksimal 2 hari per bulan) bagi seluruh karyawan wanita. |
| **`saldo:reset-tahunan`** | Setiap tanggal 1 Januari, pukul **00:00 WIB** | Memperbarui tahun kalender saldo cuti dan mengalokasikan kuota baru Cuti Tahunan (default 12 hari) untuk tahun kalender berjalan. |
| **`pengajuan:followup-wa`** | Setiap **10 menit** (Timezone: `Asia/Jakarta`) | Memindai pengajuan Cuti, CAR, dan MPR yang berstatus `pending`. Dilengkapi **Work Hours Guard** (hanya mengirim saat atasan sedang aktif bekerja: Staf Normal Senin-Jumat jam kantor, Staf Roster saat shift dinas aktif) dan **Anti-Spam Throttling** (jeda minimal 2 jam antar notifikasi). |

---

### 7.3. Perintah Manual untuk Audit & Pengujian Langsung
Anda dapat menjalankan perintah-perintah ini secara manual melalui terminal untuk pengujian:

```bash
cd /var/www/umbulan

# 1. Melihat seluruh daftar jadwal tugas yang terdaftar
php artisan schedule:list

# 2. Menguji eksekusi simulasi seluruh scheduler
php artisan schedule:test

# 3. Force Trigger Manual: Reset Saldo Haid
php artisan saldo:reset-haid

# 4. Force Trigger Manual: Reset Saldo Tahunan
php artisan saldo:reset-tahunan

# 5. Force Trigger Manual: Kirim Notifikasi Follow-Up WA Sekarang
php artisan pengajuan:followup-wa
```

---

## Langkah 8: Konfigurasi Web Server Nginx & SSL HTTPS

### 8.1. Buat File Konfigurasi Virtual Host Nginx
```bash
sudo nano /etc/nginx/sites-available/umbulan.conf
```

Masukkan konfigurasi production-ready berikut (ganti `domain-anda.com` dengan domain atau subdomain Anda):

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name domain-anda.com;

    # Redirect seluruh trafik HTTP biasa ke HTTPS
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name domain-anda.com;

    root /var/www/umbulan/public;
    index index.php index.html;

    charset utf-8;

    # Batas ukuran upload (untuk upload lampiran MPR, CAR, TTD, & foto profil)
    client_max_body_size 50M;

    # Log file
    access_log /var/log/nginx/umbulan_access.log;
    error_log /var/log/nginx/umbulan_error.log error;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Routing Utama Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Caching Static Assets (Build Vite & Storage)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    # FastCGI PHP-FPM Handler
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    # Blokir Akses ke File Sensitif (.env, .git, composer, package, markdown)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* /(composer\.(json|lock)|package(-lock)?\.json|CARA_DEPLOY\.md|SYSTEM_DOCUMENTATION\.md)$ {
        deny all;
    }
}
```

### 8.2. Aktifkan Konfigurasi & Uji Sintaks Nginx
```bash
# Aktifkan site via symbolic link
sudo ln -s /etc/nginx/sites-available/umbulan.conf /etc/nginx/sites-enabled/

# Hapus konfigurasi default Nginx (jika belum pernah dihapus)
sudo rm -f /etc/nginx/sites-enabled/default

# Uji sintaks konfigurasi Nginx
sudo nginx -t
# Output harus: syntax is ok / test is successful

# Muat ulang Nginx
sudo systemctl reload nginx
```

### 8.3. Pasang Sertifikat SSL Gratis (Let's Encrypt Certbot)
Pastikan domain Anda (`domain-anda.com`) sudah diarahkan (*DNS A Record*) ke alamat IP publik server, kemudian jalankan:

```bash
sudo certbot --nginx -d domain-anda.com
```
Ikuti instruksi di layar. Certbot akan otomatis mengonfigurasi sertifikat SSL pada blok Nginx Anda dan mengatur pembaruan otomatis (*auto-renewal*).

---

## Langkah 9: Caching & Optimasi Produksi

Jalankan perintah optimasi bawaan Laravel agar konfigurasi, rute, dan template Blade disimpan ke dalam cache memori:

```bash
cd /var/www/umbulan

# 1. Bersihkan seluruh cache lama
php artisan optimize:clear

# 2. Kompilasi konfigurasi, rute, dan event ke cache
php artisan optimize

# 3. Kompilasi template Blade ke direktori cache
php artisan view:cache
```

> ⚠️ **Catatan Penting:** Jika Anda mengubah file `.env`, file konfigurasi di `config/`, atau menambahkan rute di `routes/web.php`, Anda **wajib** menjalankan `php artisan optimize` kembali agar perubahan terbaca oleh framework.

---

## Langkah 10: Prosedur Pemeliharaan & Update Aplikasi (CI/CD Script)

Ketika ada pembaruan kode baru dari repository Git, Anda dapat membuat file skrip otomatisasi di `/var/www/umbulan/deploy.sh`:

```bash
sudo nano /var/www/umbulan/deploy.sh
```

Isi dengan kode berikut:

```bash
#!/usr/bin/env bash
set -e

echo "🚀 Memulai proses deployment update ERP Umbulan..."

# 1. Aktifkan Maintenance Mode
php artisan down --render="errors::503" --secret="kunci-bypass-rahasia-anda"

# 2. Ambil perubahan kode terbaru dari Git
git pull origin main

# 3. Pasang dependensi PHP terbaru
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Pasang dependensi Node.js & bangun asset frontend
npm ci
npm run build

# 5. Pasang dependensi microservice WhatsApp
cd whatsapp-service
npm ci
cd ..

# 6. Jalankan migrasi database baru (jika ada)
php artisan migrate --force

# 7. Optimasi & Caching
php artisan optimize:clear
php artisan optimize
php artisan view:cache

# 8. Restart Background Services
sudo -u www-data pm2 restart umbulan-whatsapp
sudo supervisorctl restart umbulan-worker:*

# 9. Matikan Maintenance Mode
php artisan up

echo "✅ Deployment update selesai dan aplikasi kembali online!"
```

Berikan izin eksekusi pada skrip:
```bash
sudo chmod +x /var/www/umbulan/deploy.sh
```

Setiap kali Anda ingin melakukan deploy update terbaru di masa mendatang, cukup jalankan:
```bash
cd /var/www/umbulan && sudo ./deploy.sh
```

---

## Checklist Akhir Pasca-Deploy (Go-Live Verification)

- [ ] Akses web browser: `https://domain-anda.com`.
- [ ] Buka menu Administrator > **WhatsApp Gateway** (`/admin/whatsapp`).
- [ ] Pindai QR Code dengan aplikasi WhatsApp resmi perusahaan untuk menautkan nomor pengirim notifikasi.
- [ ] Lakukan uji kirim pesan WhatsApp pada fitur sandbox di halaman tersebut.
- [ ] Uji coba login akun karyawan dan pastikan presensi wajah serta geolocation GPS berfungsi normal.
- [ ] Periksa log sistem untuk memastikan tidak ada kesalahan:
  ```bash
  tail -f /var/www/umbulan/storage/logs/laravel-*.log
  sudo -u www-data pm2 logs umbulan-whatsapp
  sudo supervisorctl status
  ```
