Panduan Deployment Project Laravel (Production)

Dokumen ini berisi panduan langkah-demi-langkah untuk melakukan deployment aplikasi Laravel dari repositori Git ke server produksi (VPS Linux/Ubuntu).

📋 Prasyarat (Prerequisites)

Sebelum memulai, pastikan server kamu sudah terinstall:

PHP (Sesuai versi Laravel kamu, misal PHP 8.2 atau 8.3)

Composer

Web Server (Nginx atau Apache)

Database Server (MySQL / PostgreSQL)

🛠️ Langkah-Langkah Deployment

1. Masuk ke Server & Clone Repository

Gunakan SSH untuk masuk ke server kamu, lalu masuk ke direktori web server (biasanya di /var/www/):

cd /var/www
git clone <URL_REPOSITORY_GITHUB> nama-proyek
cd nama-proyek


2. Install Dependencies (Composer)

Jalankan perintah ini untuk mengunduh semua pustaka PHP yang dibutuhkan oleh proyek tanpa menyertakan pustaka khusus development (seperti testing tools):

composer install --no-dev --optimize-autoloader


3. Konfigurasi Environment (.env)

Salin file template konfigurasi bawaan Laravel:

cp .env.example .env


Setelah menyalin, edit file .env menggunakan text editor di server (misal nano):

nano .env


⚠️ Penting: Ubah beberapa konfigurasi wajib berikut di dalam file .env:

APP_ENV=production

APP_DEBUG=false

APP_URL=https://domain-kamu.com

MAIL_MAILER=smtp

MAIL_SCHEME=null

MAIL_HOST=

MAIL_PORT=

MAIL_USERNAME=

MAIL_PASSWORD=

MAIL_FROM_ADDRESS=

FONNTE_TOKEN=

Sesuaikan DB_DATABASE, DB_USERNAME, dan DB_PASSWORD dengan database server kamu.

Tekan CTRL + O lalu Enter untuk menyimpan, dan CTRL + X untuk keluar dari nano.

4. Generate Application Key

Buat kunci pengaman aplikasi baru untuk enkripsi data, token, dan session:

php artisan key:generate


5. Migrasi Database (Database Migration)

Jalankan migrasi database untuk membuat tabel-tabel yang diperlukan aplikasi di server produksi:

php artisan migrate --force


Bendera --force wajib digunakan di server produksi untuk melewati pertanyaan konfirmasi keamanan.

6. Hubungkan Folder Penyimpanan (Storage Link)

Buat tautan simbolis (symlink) agar file/foto yang diunggah oleh user (yang tersimpan di folder private storage) bisa diakses secara publik oleh browser:

php artisan storage:link


7. Atur Izin Folder (Permissions)

Web server (Nginx/Apache) memerlukan hak akses menulis (write permission) pada folder storage dan cache. Jalankan perintah berikut agar tidak terjadi error Permission Denied (Error 500):

# Ubah kepemilikan folder ke user web server (www-data)
sudo chown -R www-data:www-data storage bootstrap/cache

# Atur izin akses folder agar bisa dibaca dan ditulis oleh web server
sudo chmod -R 775 storage bootstrap/cache


8. Optimasi Performa (Caching)

Gunakan perintah optimasi bawaan Laravel agar aplikasi berjalan jauh lebih cepat di server produksi:

php artisan optimize

Perintah ini akan melakukan cache otomatis terhadap konfigurasi dan rute aplikasi.

⏰ Konfigurasi Task Scheduler (Otomatisasi)

Laravel menggunakan fitur Task Scheduling untuk menjalankan tugas-tugas otomatis (seperti hapus data sampah, kirim email massal, dll).

Di lingkungan produksi, kamu tidak boleh menggunakan php artisan schedule:work. Sebagai gantinya, daftarkan perintah scheduler ke Cron Job milik server.

Cara Mengaktifkan:

Buka halaman konfigurasi Cron Job server kamu:

crontab -e


Jika server meminta memilih editor, pilih nano (ketik 1 lalu Enter).

Gulir ke bagian paling bawah file, lalu tempel (paste) baris perintah berikut:

* * * * * cd /var/www/nama-proyek && php artisan schedule:run >> /dev/null 2>&1


💡 Ubah /var/www/nama-proyek dengan lokasi folder proyek Laravel kamu yang sebenarnya di server.

Simpan perubahan dengan menekan CTRL + O -> Enter, lalu keluar menggunakan CTRL + X.

Sekarang server Linux kamu akan otomatis memanggil scheduler Laravel setiap 1 menit tanpa henti!

🎉 Selesai! Aplikasi Laravel kamu sekarang sudah aktif, aman, dan berjalan otomatis di web server produksi.
