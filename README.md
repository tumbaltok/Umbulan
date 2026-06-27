<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


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
```bash
cd /var/www
git clone <URL_REPOSITORY_GITHUB> nama-proyek
cd nama-proyek
```


2. Install Dependencies (Composer)

Jalankan perintah ini untuk mengunduh semua pustaka PHP yang dibutuhkan oleh proyek tanpa menyertakan pustaka khusus development (seperti testing tools):
```bash
composer install --no-dev --optimize-autoloader
```

3. Konfigurasi Environment (.env)

Salin file template konfigurasi bawaan Laravel:
```bash
cp .env.example .env
```

Setelah menyalin, edit file .env menggunakan text editor di server (misal nano):
```bash
nano .env
```

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

TOKEN=

Sesuaikan DB_DATABASE, DB_USERNAME, dan DB_PASSWORD dengan database server kamu.

Tekan CTRL + O lalu Enter untuk menyimpan, dan CTRL + X untuk keluar dari nano.

4. Generate Application Key

Buat kunci pengaman aplikasi baru untuk enkripsi data, token, dan session:
```bash
php artisan key:generate
```

5. Migrasi Database (Database Migration)

Jalankan migrasi database untuk membuat tabel-tabel yang diperlukan aplikasi di server produksi:
```bash
php artisan migrate --force
```

Bendera --force wajib digunakan di server produksi untuk melewati pertanyaan konfirmasi keamanan.

6. Hubungkan Folder Penyimpanan (Storage Link)

Buat tautan simbolis (symlink) agar file/foto yang diunggah oleh user (yang tersimpan di folder private storage) bisa diakses secara publik oleh browser:
```bash
php artisan storage:link
```

7. Atur Izin Folder (Permissions)

Web server (Nginx/Apache) memerlukan hak akses menulis (write permission) pada folder storage dan cache. Jalankan perintah berikut agar tidak terjadi error Permission Denied (Error 500):

# Ubah kepemilikan folder ke user web server (www-data)
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
```
# Atur izin akses folder agar bisa dibaca dan ditulis oleh web server
```bash
sudo chmod -R 775 storage bootstrap/cache
```

8. Optimasi Performa (Caching)

Gunakan perintah optimasi bawaan Laravel agar aplikasi berjalan jauh lebih cepat di server produksi:
```bash
php artisan optimize
```
Perintah ini akan melakukan cache otomatis terhadap konfigurasi dan rute aplikasi.

⏰ Konfigurasi Task Scheduler (Otomatisasi)

Laravel menggunakan fitur Task Scheduling untuk menjalankan tugas-tugas otomatis (seperti hapus data sampah, kirim email massal, dll).

Di lingkungan produksi, kamu tidak boleh menggunakan php artisan schedule:work. Sebagai gantinya, daftarkan perintah scheduler ke Cron Job milik server.

Cara Mengaktifkan:

Buka halaman konfigurasi Cron Job server kamu:

crontab -e


Jika server meminta memilih editor, pilih nano (ketik 1 lalu Enter).

Gulir ke bagian paling bawah file, lalu tempel (paste) baris perintah berikut:
```bash
cd /var/www/nama-proyek && php artisan schedule:run >> /dev/null 2>&1
```


💡 Ubah /var/www/nama-proyek dengan lokasi folder proyek Laravel kamu yang sebenarnya di server.

Simpan perubahan dengan menekan CTRL + O -> Enter, lalu keluar menggunakan CTRL + X.

Sekarang server Linux kamu akan otomatis memanggil scheduler Laravel setiap 1 menit tanpa henti!

🎉 Selesai! Aplikasi Laravel kamu sekarang sudah aktif, aman, dan berjalan otomatis di web server produksi.
