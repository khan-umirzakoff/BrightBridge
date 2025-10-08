#!/bin/bash
# Agar biror buyruq xato bilan yakunlansa, skriptni darhol to'xtatish
set -e

echo "🚀 Loyiha sozlamalari boshlanmoqda..."

# Loyiha papkasiga o'tish
cd public_html

# 1. .env faylini .env.example'dan yaratish (agar mavjud bo'lmasa)
if [ ! -f .env ]; then
    echo "📄 .env fayli yaratilmoqda..."
    cp .env.example .env
else
    echo "👍 .env fayli allaqachon mavjud."
fi

# 2. Composer bog'liqliklarini o'rnatish
echo "📦 Composer paketlari o'rnatilmoqda..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 3. Laravel ilova kalitini yaratish
echo "🔑 Ilova kaliti (APP_KEY) yaratilmoqda..."
php artisan key:generate

# 4. Keshni tozalash
echo "🧹 Eskirgan kesh tozalanmoqda..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Ma'lumotlar bazasi migratsiyasini ishga tushirish
echo "🏗️ Ma'lumotlar bazasi jadvallari (migratsiya) yaratilmoqda..."
php artisan migrate

echo "--------------------------------------------------"
echo "✅ Sozlash muvaffaqiyatli yakunlandi!"
echo ""
echo "DIQQAT: `public_html/.env` faylini ochib, ma'lumotlar bazasi (DB_DATABASE, DB_USERNAME, DB_PASSWORD) va boshqa kerakli sozlamalarni to'ldiring."
echo ""
echo "Dasturni ishga tushirish uchun quyidagi buyruqni kiriting:"
echo "cd public_html && php artisan serve"
echo "--------------------------------------------------"
