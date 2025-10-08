#!/bin/bash
# Agar biror buyruq xato bilan yakunlansa, skriptni darhol to'xtatish
set -e

echo "🚀 To'liq muhitni sozlash skripti ishga tushirildi (Yakuniy, ishonchli versiya)..."
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

# 3. Barcha eski keshni to'liq yo'q qilish
echo "🧹 Barcha eski keshlar o'chirilmoqda..."
rm -f bootstrap/cache/*.php

# 4. BOSQICH 1: Skriptlarsiz Composer install
echo "📦 1/4: Bog'liqliklar skriptlarsiz o'rnatilmoqda..."
php composer.phar install --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# 5. BOSQICH 2: Konfiguratsiyani keshga yozish
# Endi .env fayli to'g'ri bo'lgani uchun bu qadam xavfsiz
echo "⚙️ 2/4: Konfiguratsiya majburan keshga yozilmoqda..."
php artisan config:cache

# 6. BOSQICH 3: Composer skriptlarini ishga tushirish
echo "🚀 3/4: Composer skriptlari endi xavfsiz ishga tushirilmoqda..."
php composer.phar dump-autoload --optimize
php artisan package:discover --ansi

# 7. BOSQICH 4: Ma'lumotlar bazasini sozlash
echo "🛠️ 4/4: Ma'lumotlar bazasi sozlanmoqda..."
DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "DROP DATABASE IF EXISTS \`$DB_DATABASE\`; CREATE DATABASE \`$DB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < ../brightbr_job.sql
echo "✅ Ma'lumotlar bazasi muvaffaqiyatli import qilindi."

# 8. Yakuniy tozalash
php artisan view:clear
php artisan route:clear

echo "------------------------------------------------------------"
echo "✅ MUHITNI SOZLASH TO'LIQ VA MUVAFFAQIYATLI YAKUNLANDI!"
echo ""
echo "Dasturni ishga tushirish uchun quyidagi buyruqni kiriting:"
echo "cd public_html && php artisan serve"
echo "------------------------------------------------------------"