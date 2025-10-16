#!/bin/bash
# BrightBridge Production Deployment via cPanel Terminal
# Usage on server: bash deploy_via_terminal.sh

set -e  # Exit on error

echo "============================================================"
echo "  BrightBridge Production Deployment (Terminal)"
echo "============================================================"
echo ""

# Change to public_html directory
if [ ! -f "artisan" ]; then
    if [ -d "public_html" ]; then
        cd public_html
    else
        echo "❌ Error: Cannot find Laravel project (artisan file missing)"
        exit 1
    fi
fi

echo "✅ Working directory: $(pwd)"
echo ""

# Step 1: Check PHP version
echo "[1/8] Checking PHP version..."
PHP_VERSION=$(php -v | head -n 1)
echo "     $PHP_VERSION"
echo ""

# Step 2: Check if composer exists
echo "[2/8] Checking Composer..."
if [ -f "composer.phar" ]; then
    echo "     ✅ composer.phar found"
    COMPOSER_CMD="php composer.phar"
elif command -v composer &> /dev/null; then
    echo "     ✅ composer command found"
    COMPOSER_CMD="composer"
else
    echo "     ⏳ Downloading composer.phar..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    php -r "unlink('composer-setup.php');"
    echo "     ✅ composer.phar downloaded"
    COMPOSER_CMD="php composer.phar"
fi
echo ""

# Step 3: Backup current .env (if exists)
echo "[3/8] Backing up configuration..."
if [ -f ".env" ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo "     ✅ .env backed up"
else
    echo "     ⚠️  No .env found - you'll need to create it"
fi
echo ""

# Step 4: Install dependencies
echo "[4/8] Installing dependencies (may take 1-2 minutes)..."
$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction
echo "     ✅ Dependencies installed"
echo ""

# Step 5: Set permissions
echo "[5/8] Setting permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
echo "     ✅ Permissions set"
echo ""

# Step 6: Clear all caches
echo "[6/8] Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "     ✅ Caches cleared"
echo ""

# Step 7: Optimize for production
echo "[7/8] Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "     ✅ Optimization complete"
echo ""

# Step 8: Verify installation
echo "[8/8] Verifying installation..."
echo ""

# Check Laravel version
echo "Laravel Version:"
php artisan --version
echo ""

# Check critical files
if [ -f "vendor/autoload.php" ]; then
    echo "✅ vendor/autoload.php exists"
else
    echo "❌ vendor/autoload.php missing!"
fi

if [ -f ".env" ]; then
    echo "✅ .env file exists"
else
    echo "⚠️  .env file missing - create it before testing!"
fi

if [ -d "storage/logs" ]; then
    echo "✅ storage/logs directory exists"
else
    echo "❌ storage/logs directory missing!"
fi

echo ""
echo "============================================================"
echo "  DEPLOYMENT COMPLETED!"
echo "============================================================"
echo ""
echo "✅ Dependencies installed"
echo "✅ Permissions set"
echo "✅ Laravel optimized"
echo ""
echo "NEXT STEPS:"
echo ""
echo "1. Make sure .env file is configured:"
echo "   - APP_ENV=production"
echo "   - APP_DEBUG=false"
echo "   - Database credentials"
echo "   - API keys (GEMINI_API_KEY, OPENAI_API_KEY)"
echo ""
echo "2. Test your application:"
echo "   https://brightbridge.uz"
echo "   https://brightbridge.uz/admin/site"
echo ""
echo "3. Check logs for errors:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "============================================================"
