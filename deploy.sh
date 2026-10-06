#!/usr/bin/env bash
# ==============================================================================
# ERP META ADHYA TIRTA UMBULAN - UNIVERSAL ONE-CLICK PRODUCTION DEPLOYER
# Dioptimalkan khusus untuk aaPanel, Ubuntu/Debian VPS, dan On-Premise Server
# ==============================================================================
set -e

# 1. Tentukan Direktori Proyek Utama
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

echo "=================================================================="
echo "  🚀 MEMULAI ONE-CLICK DEPLOYMENT: ERP META ADHYA TIRTA UMBULAN   "
echo "  📁 Lokasi Proyek: $APP_DIR                                      "
echo "=================================================================="

# 2. Deteksi Path PHP (Khusus kompatibilitas aaPanel & Linux Standar)
if [ -f "/www/server/php/83/bin/php" ]; then
    PHP_BIN="/www/server/php/83/bin/php"
elif [ -f "/www/server/php/84/bin/php" ]; then
    PHP_BIN="/www/server/php/84/bin/php"
elif [ -f "/www/server/php/82/bin/php" ]; then
    PHP_BIN="/www/server/php/82/bin/php"
elif command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
else
    echo "❌ [ERROR] PHP tidak ditemukan di sistem. Harap install PHP 8.3 terlebih dahulu."
    exit 1
fi
echo "🐘 [PHP] Menggunakan interpreter: $($PHP_BIN -v | head -n 1)"

# 3. Deteksi Path Composer (Auto-install composer.phar jika belum ada)
if command -v composer >/dev/null 2>&1; then
    COMPOSER_CMD="composer"
elif [ -f "/www/server/php/83/bin/composer" ]; then
    COMPOSER_CMD="$PHP_BIN /www/server/php/83/bin/composer"
elif [ -f "$APP_DIR/composer.phar" ]; then
    COMPOSER_CMD="$PHP_BIN $APP_DIR/composer.phar"
else
    echo "📥 [COMPOSER] Mengunduh composer.phar otomatis..."
    curl -sS https://getcomposer.org/installer | $PHP_BIN
    COMPOSER_CMD="$PHP_BIN $APP_DIR/composer.phar"
fi

# 4. Deteksi / Inisialisasi File Lingkungan (.env) & APP_KEY
if [ ! -f "$APP_DIR/.env" ]; then
    echo "📝 [ENV] File .env belum ditemukan. Menginisialisasi otomatis dari .env.example..."
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"

    # Jika terminal interaktif (dijalankan langsung di terminal/SSH), tawarkan konfigurasi DB langsung
    if [ -t 0 ]; then
        echo ""
        echo "------------------------------------------------------------------"
        echo "  🛠️  SETUP DATABASE CEPAT (Tekan ENTER untuk lewati / edit manual nanti)"
        echo "------------------------------------------------------------------"
        read -p "  👉 Nama Database MySQL di aaPanel : " INPUT_DB_NAME
        read -p "  👉 Username Database MySQL         : " INPUT_DB_USER
        read -sp "  👉 Password Database MySQL         : " INPUT_DB_PASS
        echo ""
        
        if [ -n "$INPUT_DB_NAME" ]; then
            sed -i "s/^DB_DATABASE=.*/DB_DATABASE=$INPUT_DB_NAME/" "$APP_DIR/.env"
        fi
        if [ -n "$INPUT_DB_USER" ]; then
            sed -i "s/^DB_USERNAME=.*/DB_USERNAME=$INPUT_DB_USER/" "$APP_DIR/.env"
        fi
        if [ -n "$INPUT_DB_PASS" ]; then
            ESCAPED_PASS=$(printf '%s\n' "$INPUT_DB_PASS" | sed -e 's/[\/&]/\\&/g')
            sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=$ESCAPED_PASS/" "$APP_DIR/.env"
        fi
        echo "  ✅ Kredensial database berhasil disimpan ke file .env"
        echo "------------------------------------------------------------------"
    else
        echo "⚠️ [PENTING] File .env telah dibuat dari template. Sesuaikan kredensial DB di file .env."
    fi
fi

# 5. Trap Handler: Jamin aplikasi kembali ONLINE jika terjadi error saat proses deploy
cleanup() {
    EXIT_CODE=$?
    if [ $EXIT_CODE -ne 0 ]; then
        echo ""
        echo "❌ [DEPLOY ERROR] Terjadi kegagalan pada status code $EXIT_CODE!"
        echo "🔄 Memastikan status aplikasi kembali ONLINE..."
        if [ -d "$APP_DIR/vendor" ]; then
            $PHP_BIN artisan up 2>/dev/null || true
        fi
    fi
}
trap cleanup EXIT ERR

# 6. Mode Pemeliharaan (Maintenance Mode)
# Hanya aktifkan jika vendor sudah tersedia (bukan clone pertama kali)
IS_BOOTABLE=false
if [ -d "$APP_DIR/vendor" ] && [ -f "$APP_DIR/vendor/autoload.php" ]; then
    IS_BOOTABLE=true
    BYPASS_SECRET=$(openssl rand -hex 16 2>/dev/null || echo "umbulan_maint_$(date +%s)")
    echo "🔒 [1/11] Mengaktifkan Mode Pemeliharaan Sementara..."
    echo "🔑 Bypass Token: $BYPASS_SECRET"
    $PHP_BIN artisan down --render="errors::503" --secret="$BYPASS_SECRET" 2>/dev/null || true
else
    echo "✨ [1/11] Inisialisasi awal instalasi (mode pemeliharaan dilewati)..."
fi

# 7. Tarik Pembaruan Kode Terbaru dari Git (jika repository git terkonfigurasi)
if [ -d "$APP_DIR/.git" ]; then
    echo "📥 [2/11] Memeriksa pembaruan kode Git..."
    git pull origin main 2>/dev/null || git pull 2>/dev/null || echo "ℹ️ Git pull dilewati atau sudah versi terbaru."
fi

# 8. Install & Perbarui Dependensi PHP (Composer)
echo "📦 [3/11] Memasang dependensi PHP (Composer)..."
$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction

# 9. Pastikan APP_KEY Terbuat
if ! grep -q "^APP_KEY=base64:" "$APP_DIR/.env"; then
    echo "🔑 [4/11] Menghasilkan APP_KEY produksi baru..."
    $PHP_BIN artisan key:generate --force
fi

# 10. Memeriksa & Menginstall Node.js / NPM jika belum ada
if ! command -v npm >/dev/null 2>&1; then
    echo "⚠️ [NPM] Node.js/NPM tidak terdeteksi di PATH sistem."
    echo "ℹ️ Jika menggunakan aaPanel, silakan install 'Node.js Version Manager' di App Store aaPanel."
fi

# 11. Membangun Asset Frontend (Vite & Tailwind CSS)
if command -v npm >/dev/null 2>&1 && [ -f "$APP_DIR/package.json" ]; then
    echo "🎨 [5/11] Membangun asset frontend Vite & Tailwind CSS..."
    npm ci 2>/dev/null || npm install --no-audit --no-fund
    npm run build
fi

# 12. Setup Microservice WhatsApp Gateway (Node.js Baileys)
if [ -d "$APP_DIR/whatsapp-service" ]; then
    echo "🤖 [6/11] Mempersiapkan WhatsApp Gateway Microservice..."
    cd "$APP_DIR/whatsapp-service"
    mkdir -p auth_session
    
    if command -v npm >/dev/null 2>&1; then
        npm ci 2>/dev/null || npm install --no-audit --no-fund
    fi

    # Pastikan PM2 tersedia untuk background process manager
    if ! command -v pm2 >/dev/null 2>&1; then
        echo "📦 Menginstall PM2 Process Manager secara global..."
        npm install -g pm2 2>/dev/null || true
    fi

    if command -v pm2 >/dev/null 2>&1; then
        echo "🚀 Menjalankan WhatsApp Gateway di latar belakang via PM2 (Port 3001)..."
        if pm2 describe umbulan-whatsapp >/dev/null 2>&1; then
            pm2 restart umbulan-whatsapp
        else
            pm2 start server.js --name umbulan-whatsapp
        fi
        pm2 save 2>/dev/null || true
    else
        echo "⚠️ PM2 tidak dapat diinstall global. Microservice dapat dijalankan via: cd whatsapp-service && npm start"
    fi
    cd "$APP_DIR"
fi

# 13. Migrasi Database & Symlink Storage
echo "🗄️ [7/11] Memeriksa koneksi database & menjalankan migrasi..."
if $PHP_BIN artisan migrate:status >/dev/null 2>&1; then
    $PHP_BIN artisan migrate --force
    echo "✅ Database berhasil dimigrasikan."
else
    echo "⚠️ [PERINGATAN] Database belum dapat dihubungi. Pastikan DB_DATABASE, DB_USERNAME, dan DB_PASSWORD pada file .env sudah sesuai dengan database server Anda."
fi

echo "🔗 [8/11] Memastikan Symlink Storage Aktif..."
$PHP_BIN artisan storage:link 2>/dev/null || true

# 14. Menjalankan Background Queue Worker via PM2 (Fallback jika belum ada Supervisor)
echo "⚙️ [9/11] Mengonfigurasi Laravel Queue Worker..."
if command -v pm2 >/dev/null 2>&1; then
    if pm2 describe umbulan-worker >/dev/null 2>&1; then
        pm2 restart umbulan-worker
    else
        pm2 start "$PHP_BIN artisan queue:work --tries=3 --timeout=90" --name umbulan-worker
    fi
    pm2 save 2>/dev/null || true
fi

# Jika Supervisor tersedia di sistem, lakukan reload juga
if command -v supervisorctl >/dev/null 2>&1; then
    sudo supervisorctl restart umbulan-worker:* 2>/dev/null || supervisorctl restart umbulan-worker:* 2>/dev/null || true
fi

# 15. Pemasangan Otomatis Cron Job (Reset Saldo Cuti Haid Bulanan, Reset Saldo Tahunan, Followup WA)
echo "⏰ [10/11] Mengonfigurasi Crontab Otomatis untuk Laravel Scheduler..."
CRON_CMD="* * * * * cd $APP_DIR && $PHP_BIN artisan schedule:run >> /dev/null 2>&1"
CURRENT_CRON=$(crontab -l 2>/dev/null || true)

if echo "$CURRENT_CRON" | grep -Fq "$APP_DIR"; then
    echo "✅ Crontab Laravel Scheduler sudah aktif."
else
    echo "➕ Mendaftarkan Laravel Scheduler ke Crontab sistem..."
    (echo "$CURRENT_CRON"; echo "$CRON_CMD") | crontab -
    echo "✅ Berhasil mendaftarkan Scheduler (Reset Saldo Bulanan, Tahunan, & Notifikasi WA aktif otomatis)."
fi

# 16. Optimasi Cache & Izin Berkas (File Permissions)
echo "⚡ [11/11] Mengoptimalkan cache sistem & menyesuaikan izin folder..."
$PHP_BIN artisan optimize:clear 2>/dev/null || true
$PHP_BIN artisan optimize 2>/dev/null || true
$PHP_BIN artisan view:cache 2>/dev/null || true

# Atur permission storage, cache, dan auth_session
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/whatsapp-service/auth_session" 2>/dev/null || true

# Berikan kepemilikan ke user web server (aaPanel: www, Ubuntu: www-data)
if id "www" >/dev/null 2>&1; then
    chown -R www:www "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/whatsapp-service/auth_session" 2>/dev/null || true
elif id "www-data" >/dev/null 2>&1; then
    chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" "$APP_DIR/whatsapp-service/auth_session" 2>/dev/null || true
fi

# 17. Kembalikan Aplikasi ke Status Online
$PHP_BIN artisan up 2>/dev/null || true
trap - EXIT ERR

echo ""
echo "=================================================================="
echo "  🎉 ONE-CLICK DEPLOYMENT SELESAI & SISTEM SIAP DIGUNAKAN!        "
echo "=================================================================="
echo "  🌐 Aplikasi Web          : ONLINE"
echo "  🤖 WhatsApp Gateway      : AKTIF (Port 3001 via PM2)"
echo "  ⚙️ Queue Worker          : AKTIF (Background Service)"
echo "  ⏰ Cron Job Scheduler    : AKTIF (Reset Saldo Tahunan/Haid & WA)"
echo "  🔗 Storage Symlink       : TERHUBUNG"
echo "------------------------------------------------------------------"
echo "  💡 CATATAN PENTING DI aaPanel:"
echo "     Pastikan di menu Website -> Settings -> Site Directory:"
echo "     'Running directory' diarahkan ke subfolder: /public"
echo "=================================================================="
