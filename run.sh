#!/bin/bash
# Agar biror buyruq xato bilan yakunlansa, skriptni darhol to\'xtatish
set -e

echo "🚀 Loyiha va ma'lumotlar bazasini to'liq sozlash boshlanmoqda..."
echo "------------------------------------------------------------"

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
echo "📦 Composer paketlari o'rnatilmoqda... (Bu biroz vaqt olishi mumkin)"
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

echo "------------------------------------------------------------"
echo "🛠️ Ma'lumotlar bazasi sozlamalari boshlanmoqda..."

# .env fayli mavjudligini tekshirish
if [ ! -f .env ]; then
    echo "❌ XATO: .env fayli topilmadi. Skriptni davom ettirib bo'lmaydi."
    exit 1
fi

# .env faylidan DB ma'lumotlarini olish
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)


# Agar ma'lumotlar to'liq bo'lmasa, xabar berish
if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo "❌ XATO: .env faylidagi DB_DATABASE yoki DB_USERNAME bo'sh. Iltimos, to'ldiring."
    exit 1
fi

echo "Baza nomi: '$DB_DATABASE'"
echo "Foydalanuvchi: '$DB_USERNAME'"
echo "SQL fayl import qilinmoqda: ../brightbr_job.sql"


# 5. MySQL'da bazani yaratish va SQL faylini import qilish
# Parol so'rovi chiqmasligi uchun -p dan keyin bo'sh joy qoldirmaslik muhim
echo "🏗️ MySQL'da '$DB_DATABASE' bazasi yaratilmoqda va ma'lumotlar import qilinmoqda..."
mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "DROP DATABASE IF EXISTS \`$DB_DATABASE\`; CREATE DATABASE \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < ../brightbr_job.sql

echo "✅ Ma'lumotlar bazasi muvaffaqiyatli import qilindi."
echo "------------------------------------------------------------"
echo "✅ SOZLANISH TO'LIQ YAKUNLANDI!"
echo ""
echo "DIQQAT: Ishni boshlashdan oldin 'public_html/.env' faylidagi sozlamalar (ayniqsa DB_PASSWORD) to'g'riligiga ishonch hosil qiling."
echo ""
echo "Dasturni ishga tushirish uchun quyidagi buyruqni kiriting:"
echo "cd public_html && php artisan serve"
echo "------------------------------------------------------------"
