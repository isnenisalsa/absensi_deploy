#!/bin/bash
# Matikan error-stop agar migrasi tidak menghentikan server jika gagal sejenak
set -e

echo "--- Tahap Persiapan Runtime ---"

# 1. Jalankan migrasi database
echo "Menjalankan migrasi database..."
php artisan migrate --force

# 2. Pastikan folder storage punya izin tulis (crucial di Railway)
chmod -R 775 storage bootstrap/cache

echo "--- Memulai FrankenPHP (High Performance Server) ---"
echo "Mendengarkan pada Port: ${PORT:-8080}"

# 3. Jalankan FrankenPHP untuk melayani folder public/
# Menggunakan --worker untuk performa Laravel yang lebih baik jika memungkinkan
./frankenphp php-server --listen :${PORT:-8080} --root public/
