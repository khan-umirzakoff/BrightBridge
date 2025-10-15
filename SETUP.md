# BrightBridge/JobCare - Development Workflow

Laravel 5.8 job portal application with AI-powered features (Uzbek: "JobCare Platformasi")

## 📋 Project Workflow

```
HOST (Production)  →  LOCAL (Development)  →  HOST (Production)
     ↓ git clone           ↓ changes              ↑ git push
     ↓ SQL dump            ↓ testing              ↑ deploy
```

Bu loyiha **hostdan** olingan, **localda** test va o'zgartirish qilinadi, keyin **hostga** qayta deploy qilinadi.

---

## 🚀 Local Development Setup (Birinchi marta)

### 1. Repository'ni clone qiling

```bash
git clone <repository-url>
cd BrightBridge
```

### 2. .env faylni yarating (LOCAL uchun)

```bash
cd public_html
cp .env.example .env
```

**MUHIM:** `.env` faylni LOCAL sozlamalar bilan to'ldiring:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Local Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=brightbridge_local
DB_USERNAME=laravel
DB_PASSWORD=laravel

# Local AI Settings (test keys)
AI_PROVIDER=gemini
GEMINI_API_KEY=your_test_api_key_here
# PRODUCTION API keys ishlatmang!
```

### 3. Setup skriptni ishga tushiring

```bash
cd ..
./run.sh
```

**Birinchi marta:** Nix o'rnatiladi, terminal qayta ochish kerak, keyin `./run.sh` ni yana bajaring.

### 4. Serverni ishga tushiring

```bash
cd public_html
nix-shell ../shell.nix --run "php artisan serve"
```

Brauzer: **http://localhost:8000**

---

## 🔄 Development Workflow

### Local'da ishlash

```bash
# 1. Serverni ishga tushiring
cd public_html
nix-shell ../shell.nix --run "php artisan serve"

# 2. Kod o'zgartirishlar qiling
# 3. Test qiling
# 4. Git'ga commit qiling
```

### O'zgarishlarni commit qilish

```bash
git add .
git commit -m "Feature: yangi funksiya qo'shildi"
git push origin main
```

---

## 📤 HOSTGA DEPLOY QILISH

### Variant 1: Git orqali (tavsiya etiladi)

Hostda SSH orqali:

```bash
# 1. Hostga SSH qiling
ssh user@your-host.com

# 2. Loyiha papkasiga o'ting
cd /var/www/brightbridge  # yoki sizning yo'lingiz

# 3. O'zgarishlarni pull qiling
git pull origin main

# 4. Dependencies yangilash (production mode)
composer install --no-dev --optimize-autoloader

# 5. Cache yangilash
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Database migration (agar kerak bo'lsa)
php artisan migrate --force

# 7. Permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Variant 2: FTP orqali (qo'lda)

1. Faqat o'zgargan fayllarni FTP orqali yuklang
2. **`.env` faylni upload qilmang!** (host'dagi .env alohida)
3. Hostda `composer install --no-dev` bajaring
4. Cache tozalang: `php artisan config:cache`

---

## ⚙️ Environment Farqlari

### LOCAL (.env)
```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=localhost
DB_USERNAME=laravel
DB_PASSWORD=laravel
GEMINI_API_KEY=test_key_here
```

### PRODUCTION/HOST (.env)
```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_USERNAME=brightbr_user
DB_PASSWORD=strong_password_here
GEMINI_API_KEY=production_api_key_here
```

**MUHIM:** Production `.env` ni Git'ga qo'shmang! `.gitignore` da bo'lishi kerak.

---

## 🛠️ Manual Commands

### Local Development

```bash
# Nix environment'ga kirish
cd public_html
nix-shell ../shell.nix

# Cache tozalash
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Database reset
sudo mysql -u root brightbridge_local < ../brightbr_job.sql

# Dependencies qayta o'rnatish
composer install
```

### Production/Host

```bash
# Cache optimizatsiya
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Maintenance mode
php artisan down
# ... deploy ...
php artisan up

# Queue worker restart (agar ishlatilsa)
php artisan queue:restart
```

---

## 🔐 Security Checklist

### Git'ga commit qilmaslik kerak:

- ❌ `.env` (production credentials)
- ❌ `storage/*.key` (encryption keys)
- ❌ `vendor/` (composer packages)
- ❌ `node_modules/` (npm packages)
- ❌ `.idea/`, `.vscode/` (IDE configs)

### `.gitignore` tekshiring:

```bash
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
```

---

## 🐛 Troubleshooting

### Local'da muammolar

**Nix not found:**
```bash
source ~/.nix-profile/etc/profile.d/nix.sh
# yoki terminal'ni qayta oching
```

**Database access denied:**
```bash
sudo mysql -u root -e "CREATE USER 'laravel'@'localhost' IDENTIFIED BY 'laravel'; GRANT ALL PRIVILEGES ON *.* TO 'laravel'@'localhost'; FLUSH PRIVILEGES;"
```

**Port 8000 band:**
```bash
php artisan serve --port=8080
```

### Host'da muammolar

**500 Internal Server Error:**
```bash
# Permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Cache clear
php artisan cache:clear
php artisan config:clear
```

**Composer errors:**
```bash
composer dump-autoload
composer install --no-dev --optimize-autoloader
```

**Database connection:**
- `.env` faylni tekshiring
- `php artisan config:clear` bajaring

---

## 📊 Database Sync

### Host'dan Local'ga database export

Hostda:
```bash
mysqldump -u username -p database_name > backup.sql
```

Local'ga yuklab:
```bash
scp user@host.com:/path/backup.sql ~/Desktop/BrightBridge/
cd ~/Desktop/BrightBridge/public_html
sudo mysql -u root brightbridge_local < ../backup.sql
```

### Local'dan Host'ga (ehtiyotkorlik bilan!)

```bash
# Local'da export
mysqldump -u laravel -plaravel brightbridge_local > local_changes.sql

# Host'ga yuklash
scp local_changes.sql user@host.com:/path/
ssh user@host.com
mysql -u user -p database_name < local_changes.sql
```

---

## 📁 Important Files

### Commit qilish kerak:
- ✅ `public_html/app/` (application code)
- ✅ `public_html/config/` (configs)
- ✅ `public_html/database/migrations/` (DB changes)
- ✅ `public_html/resources/` (views, assets)
- ✅ `public_html/routes/` (routes)
- ✅ `shell.nix` (Nix config)
- ✅ `run.sh` (setup script)

### Commit qilmaslik kerak:
- ❌ `.env`
- ❌ `vendor/`
- ❌ `node_modules/`
- ❌ `storage/logs/`
- ❌ SQL dumps (katta hajmda)

---

## 🎯 Quick Reference

### Local Development
```bash
cd ~/Desktop/BrightBridge/public_html
nix-shell ../shell.nix --run "php artisan serve"
```

### Deploy to Host
```bash
git push origin main
ssh user@host.com "cd /path && git pull && composer install --no-dev && php artisan config:cache"
```

### Database Backup
```bash
# Host
mysqldump -u user -p db_name > backup.sql
```

---

## 📞 Support

- **CLAUDE.md** - To'liq texnik dokumentatsiya
- **GitHub Issues** - Muammolar
- **Local testing** - Nix orqali izolatsiyalangan muhit

---

**Muvaffaqiyatli development va deployment! 🚀**
