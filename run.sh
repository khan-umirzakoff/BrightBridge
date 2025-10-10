#!/bin/bash
# Agar biror buyruq xato bilan yakunlansa, skriptni darhol to'xtatish
set -e

echo "🚀 To'liq muhitni sozlash skripti ishga tushirildi (Tuzatilgan kod bilan standart versiya)..."
echo "------------------------------------------------------------"

# 1. Tizim va PHP sozlamalari
echo "🐘 Tizim va PHP sozlanmoqda..."
sudo apt-get update -y > /dev/null
sudo apt-get install -y software-properties-common mysql-client > /dev/null
sudo add-apt-repository ppa:ondrej/php -y > /dev/null
sudo apt-get update -y > /dev/null
sudo apt-get install -y php7.4 php7.4-cli php7.4-common php7.4-gd php7.4-dom php7.4-curl php7.4-mysql php7.4-mbstring php7.4-zip > /dev/null
echo "✅ Tizim va PHP sozlandi."

# Loyiha papkasiga o'tish
cd public_html

# 2. Composer'ni o'rnatish
if [ ! -f "composer.phar" ]; then
    echo "📥 Composer yuklanmoqda..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php > /dev/null
    php -r "unlink('composer-setup.php');"
else
    echo "👍 Composer allaqachon mavjud."
fi

# 3. .env fayli mavjudligini tekshirish
if [ ! -f ".env" ]; then
  echo "❌ XATO: .env fayli mavjud emas. Iltimos, uni yarating."
  exit 1
fi

# 4. Keshni tozalash (har ehtimolga qarshi)
echo "🧹 Barcha eski keshlar o'chirilmoqda..."
rm -f bootstrap/cache/*.php

# 5. Composer bog'liqliklarini o'rnatish
echo "📦 Bog'liqliklar o'rnatilmoqda..."
php composer.phar install --no-interaction --prefer-dist --optimize-autoloader

# 6. Laravel ilova kalitini yaratish (agar kerak bo'lsa)
# .env faylida allaqachon mavjud, lekin bu buyruq zarar qilmaydi
php artisan key:generate --ansi

# 7. Fayl huquqlarini to'g'rilash
echo "🔒 Kerakli papkalarga yozish huquqlari berilmoqda..."
chmod -R 777 storage
chmod -R 777 bootstrap/cache
echo "✅ Fayl huquqlari sozlandi."

# 8. Ma'lumotlar bazasini sozlash
echo "🛠️ Ma'lumotlar bazasi sozlanmoqda..."
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "DROP DATABASE IF EXISTS \`$DB_DATABASE\`; CREATE DATABASE \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < ../brightbr_job.sql
echo "✅ Ma'lumotlar bazasi muvaffaqiyatli import qilindi."

# 9. Yakuniy tozalash
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "------------------------------------------------------------"
echo "✅ MUHITNI SOZLASH TO'LIQ VA MUVAFFAQIYATLI YAKUNLANDI!"
echo ""
echo "Dasturni ishga tushirish uchun quyidagi buyruqni kiriting:"
echo "cd public_html && php artisan serve"
echo "------------------------------------------------------------"