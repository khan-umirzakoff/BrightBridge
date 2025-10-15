#!/bin/bash
# BrightBridge/JobCare - Local Development Setup Script
# MAQSAD: Hostdan olingan loyihani localda test qilish va o'zgartirish
# Keyin o'zgarishlarni hostga qayta deploy qilish

set -e  # Xato bo'lsa to'xtatish

echo "============================================================"
echo "🚀 BrightBridge JobCare - LOCAL DEVELOPMENT SETUP"
echo "============================================================"
echo ""
echo "⚠️  DIQQAT: Bu skript LOCAL development uchun!"
echo "   Production/Host sozlamalari o'zgarmaydi."
echo ""

# Ranglar
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Nix o'rnatilganligini tekshirish
echo -e "${BLUE}[1/8]${NC} Nix Package Manager tekshirilmoqda..."
if ! command -v nix &> /dev/null; then
    echo -e "${YELLOW}⚠️  Nix topilmadi. O'rnatilmoqda...${NC}"
    echo "Bu 5-10 daqiqa davom etadi. Sudo parol so'raladi."
    sh <(curl -L https://nixos.org/nix/install) --daemon --yes
    echo -e "${GREEN}✅ Nix o'rnatildi!${NC}"
    echo "MUHIM: Terminal'ni yopib qayta oching, keyin yana './run.sh' bajaring!"
    exit 0
else
    echo -e "${GREEN}✅ Nix allaqachon o'rnatilgan.${NC}"
fi

# Nix ni PATH ga qo'shish
if [ -f /nix/var/nix/profiles/default/etc/profile.d/nix-daemon.sh ]; then
    . /nix/var/nix/profiles/default/etc/profile.d/nix-daemon.sh
fi
export PATH="/nix/var/nix/profiles/default/bin:$PATH"

# 2. shell.nix mavjudligini tekshirish
echo -e "${BLUE}[2/8]${NC} Nix environment konfiguratsiyasi tekshirilmoqda..."
if [ ! -f "shell.nix" ]; then
    echo -e "${RED}❌ shell.nix topilmadi!${NC}"
    exit 1
fi
echo -e "${GREEN}✅ shell.nix topildi.${NC}"

# 3. MariaDB/MySQL o'rnatish va ishga tushirish
echo -e "${BLUE}[3/8]${NC} MariaDB tekshirilmoqda..."
if ! command -v mysql &> /dev/null; then
    echo -e "${YELLOW}⚠️  MariaDB topilmadi. O'rnatilmoqda...${NC}"
    sudo apt update -qq
    sudo apt install -y mariadb-server mariadb-client -qq
    sudo systemctl start mariadb
    sudo systemctl enable mariadb
    echo -e "${GREEN}✅ MariaDB o'rnatildi va ishga tushdi.${NC}"
else
    echo -e "${GREEN}✅ MariaDB allaqachon o'rnatilgan.${NC}"
    # MariaDB ishga tushirishga harakat qilish
    if ! sudo systemctl is-active --quiet mariadb 2>/dev/null; then
        echo "MariaDB ishga tushirilmoqda..."
        sudo systemctl start mariadb || true
    fi
fi

# 4. Database user yaratish
echo -e "${BLUE}[4/8]${NC} Database user sozlanmoqda..."
cd public_html

# .env fayldan ma'lumotlarni o'qish
if [ ! -f ".env" ]; then
    echo -e "${RED}❌ .env fayli topilmadi!${NC}"
    echo "Iltimos .env.example dan .env yaratib, kerakli sozlamalarni kiriting."
    exit 1
fi

DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

echo "Database user yaratilmoqda: $DB_USERNAME"
sudo mysql -u root <<EOF 2>/dev/null || true
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO '${DB_USERNAME}'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF
echo -e "${GREEN}✅ Database user yaratildi: $DB_USERNAME${NC}"

# 5. Database yaratish va import qilish
echo -e "${BLUE}[5/8]${NC} Database import qilinmoqda..."
echo "Database yaratilmoqda: $DB_DATABASE"
sudo mysql -u root <<EOF
DROP DATABASE IF EXISTS \`${DB_DATABASE}\`;
CREATE DATABASE \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF

if [ -f "../brightbr_job.sql" ]; then
    echo "SQL dump import qilinmoqda..."
    sudo mysql -u root "${DB_DATABASE}" < ../brightbr_job.sql
    echo -e "${GREEN}✅ Database muvaffaqiyatli import qilindi!${NC}"
else
    echo -e "${YELLOW}⚠️  brightbr_job.sql topilmadi. Faqat bo'sh database yaratildi.${NC}"
fi

# 6. Composer dependencies o'rnatish
echo -e "${BLUE}[6/8]${NC} Composer dependencies o'rnatilmoqda..."
nix-shell ../shell.nix --run "composer install --no-interaction --prefer-dist --optimize-autoloader"
echo -e "${GREEN}✅ Dependencies o'rnatildi.${NC}"

# 7. Laravel sozlamalari
echo -e "${BLUE}[7/8]${NC} Laravel sozlamalari..."

# Permissions
chmod -R 777 storage bootstrap/cache

# Cache tozalash
nix-shell ../shell.nix --run "php artisan config:clear"
nix-shell ../shell.nix --run "php artisan cache:clear"
nix-shell ../shell.nix --run "php artisan view:clear"

# APP_KEY generate (agar bo'sh bo'lsa)
if ! grep -q "APP_KEY=base64:" .env; then
    echo "APP_KEY generatsiya qilinmoqda..."
    nix-shell ../shell.nix --run "php artisan key:generate"
fi

echo -e "${GREEN}✅ Laravel sozlamalari tugallandi.${NC}"

# 8. Server ishga tushirish (test)
echo -e "${BLUE}[8/8]${NC} Server test qilinmoqda..."
nix-shell ../shell.nix --run "php artisan --version"

echo ""
echo "============================================================"
echo -e "${GREEN}✅ LOCAL DEVELOPMENT MUHIT TAYYOR!${NC}"
echo "============================================================"
echo ""
echo "🌐 Serverni ishga tushirish:"
echo "   cd public_html"
echo "   nix-shell ../shell.nix --run \"php artisan serve\""
echo ""
echo "   Keyin: http://localhost:8000"
echo ""
echo "============================================================"
echo -e "${YELLOW}📤 HOSTGA DEPLOY QILISH:${NC}"
echo "============================================================"
echo ""
echo "1. O'zgarishlarni Git'ga commit qiling:"
echo "   git add ."
echo "   git commit -m \"Your changes\""
echo "   git push origin main"
echo ""
echo "2. Hostda (SSH orqali):"
echo "   cd /path/to/project"
echo "   git pull origin main"
echo "   composer install --no-dev --optimize-autoloader"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo ""
echo "3. Database o'zgarishlar bo'lsa:"
echo "   php artisan migrate --force"
echo ""
echo -e "${RED}⚠️  ESLATMA:${NC}"
echo "   - .env faylni hostda ALOHIDA saqlang!"
echo "   - Production API keys localda ishlatmang!"
echo "   - Hostda Nix KERAK EMAS (oddiy PHP 7.4 yetarli)"
echo ""
echo "============================================================"
