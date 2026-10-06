#!/usr/bin/env bash
set -e

# ==============================================================================
# ERP META ADHYA TIRTA UMBULAN - SECURE PRODUCTION DEPLOYMENT SCRIPT
# ==============================================================================

# [SEC-11 FIX] Trap Handler: Pastikan aplikasi selalu kembali 'UP' jika terjadi kegagalan
cleanup() {
    EXIT_CODE=$?
    if [ $EXIT_CODE -ne 0 ]; then
        echo "❌ [DEPLOY ERROR] Deployment gagal dengan status code $EXIT_CODE!"
        echo "🔄 Mengembalikan status aplikasi ke ONLINE agar tidak terjadi downtime permanen..."
        php artisan up || true
    fi
}
trap cleanup EXIT ERR

# [SEC-11 FIX] Generate secret token maintenance secara acak dan dinamis
BYPASS_SECRET=$(openssl rand -hex 16 2>/dev/null || echo "umbulan_maint_$(date +%s)")
echo "🚀 [1/9] Mengaktifkan Mode Pemeliharaan (Maintenance Mode)..."
echo "🔑 Bypass Token Sementara: $BYPASS_SECRET"
php artisan down --render="errors::503" --secret="$BYPASS_SECRET"

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
sudo -u www-data pm2 restart umbulan-whatsapp || true
sudo supervisorctl restart umbulan-worker:* || true

echo "🌐 [9/9] Mematikan Mode Pemeliharaan..."
php artisan up

# Hapus trap karena deploy selesai dengan sukses
trap - EXIT ERR
echo "✅ Deployment update selesai! Aplikasi ERP Umbulan telah kembali beroperasi normal."
