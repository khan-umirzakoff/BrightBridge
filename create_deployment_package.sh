#!/bin/bash
# BrightBridge Production Deployment Package Creator (Linux)
# Usage: bash create_deployment_package.sh [with-vendor|without-vendor|terminal]
# Modes:
#   with-vendor    - Full package with vendor/ (~50-100MB, no Terminal needed)
#   without-vendor - Lite package without vendor/ (~5-15MB, needs Terminal)
#   terminal       - Optimized for cPanel Terminal (includes deploy script)

set -e  # Exit on error

PACKAGE_TYPE="${1:-terminal}"  # Default: terminal (since you have Terminal access!)

echo "============================================================"
echo "  BrightBridge Production Deployment Package (Linux)"
echo "============================================================"
echo ""
echo "Package type: $PACKAGE_TYPE"
echo ""

cd public_html

# Step 1: Clean logs and cache
echo "[1/6] Cleaning logs and cache..."
rm -f storage/logs/*.log
rm -f storage/framework/cache/data/*
rm -f storage/framework/sessions/*
rm -f storage/framework/views/*
echo "     Done!"

# Step 2: Remove test files
echo "[2/6] Removing test files..."
rm -f ../test_*.php
rm -f ../check_*.php
rm -f test_login.php
rm -f app/Http/Controllers/*.broken
echo "     Done!"

# Step 3: Clear Laravel cache
echo "[3/6] Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "     Done!"

# Step 4: Install/Update dependencies (if with-vendor)
if [ "$PACKAGE_TYPE" = "with-vendor" ]; then
    echo "[4/6] Installing production dependencies..."

    # Check if composer is available
    if command -v composer &> /dev/null; then
        COMPOSER_CMD="composer"
    elif [ -f "composer.phar" ]; then
        COMPOSER_CMD="php composer.phar"
    else
        echo "     Downloading composer.phar..."
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php
        php -r "unlink('composer-setup.php');"
        COMPOSER_CMD="php composer.phar"
    fi

    # Install dependencies
    $COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction
    echo "     Done!"

    # Optimize for production
    echo "[5/6] Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo "     Done!"
else
    echo "[4/6] Skipping composer install (without-vendor mode)"
    echo "     Done!"
    echo "[5/6] Skipping optimization (without-vendor mode)"
    echo "     Done!"
fi

# Step 5: Create .env.production template
echo "[6/6] Creating .env.production template..."
if [ -f ".env.example" ]; then
    cp .env.example .env.production
else
    cat > .env.production << 'EOF'
APP_NAME=BrightBridge
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://brightbridge.uz

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=brightbr_job
DB_USERNAME=brightbr_admin
DB_PASSWORD=

GEMINI_API_KEY=
OPENAI_API_KEY=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
EOF
fi
echo "     Done!"

# Step 6: Create ZIP package
echo ""
echo "Creating deployment package..."
cd ..

# Copy deploy script to root for easy access
if [ -f "deploy_via_terminal.sh" ]; then
    cp deploy_via_terminal.sh public_html/deploy_via_terminal.sh
    echo "✅ Deploy script added to package"
fi

if [ "$PACKAGE_TYPE" = "with-vendor" ]; then
    ARCHIVE_NAME="brightbridge-deploy-full.zip"

    # Create ZIP with vendor/
    zip -r "$ARCHIVE_NAME" public_html \
        -x "public_html/.env" \
        -x "public_html/.git/*" \
        -x "public_html/node_modules/*" \
        -x "public_html/storage/logs/*" \
        -x "public_html/storage/framework/cache/*" \
        -x "public_html/storage/framework/sessions/*" \
        -x "public_html/storage/framework/views/*.php" \
        -x "public_html/bootstrap/cache/*.php" \
        -x "public_html/*.backup" \
        -x "public_html/*test*.php" \
        -x "public_html/*check*.php" \
        -x "*.broken"

elif [ "$PACKAGE_TYPE" = "terminal" ]; then
    ARCHIVE_NAME="brightbridge-deploy-terminal.zip"

    # Create ZIP without vendor/ but with deploy script
    zip -r "$ARCHIVE_NAME" public_html \
        -x "public_html/.env" \
        -x "public_html/.git/*" \
        -x "public_html/vendor/*" \
        -x "public_html/node_modules/*" \
        -x "public_html/storage/logs/*" \
        -x "public_html/storage/framework/cache/*" \
        -x "public_html/storage/framework/sessions/*" \
        -x "public_html/storage/framework/views/*.php" \
        -x "public_html/bootstrap/cache/*.php" \
        -x "public_html/*.backup" \
        -x "public_html/*test*.php" \
        -x "public_html/*check*.php" \
        -x "*.broken"
else
    ARCHIVE_NAME="brightbridge-deploy-lite.zip"

    # Create ZIP without vendor/
    zip -r "$ARCHIVE_NAME" public_html \
        -x "public_html/.env" \
        -x "public_html/.git/*" \
        -x "public_html/vendor/*" \
        -x "public_html/node_modules/*" \
        -x "public_html/storage/logs/*" \
        -x "public_html/storage/framework/cache/*" \
        -x "public_html/storage/framework/sessions/*" \
        -x "public_html/storage/framework/views/*.php" \
        -x "public_html/bootstrap/cache/*.php" \
        -x "public_html/*.backup" \
        -x "public_html/*test*.php" \
        -x "public_html/*check*.php" \
        -x "*.broken"
fi

# Clean up
rm -f public_html/deploy_via_terminal.sh

echo ""
echo "============================================================"
echo "  DEPLOYMENT PACKAGE CREATED SUCCESSFULLY!"
echo "============================================================"
echo ""
echo "Package: $ARCHIVE_NAME"
echo "Size: $(du -h "$ARCHIVE_NAME" | cut -f1)"
echo ""

if [ "$PACKAGE_TYPE" = "with-vendor" ]; then
    echo "MODE: Full Package (with vendor/)"
    echo ""
    echo "WHAT'S INCLUDED:"
    echo "  ✓ All application code"
    echo "  ✓ vendor/ directory (ready to use)"
    echo "  ✓ Optimized Laravel caches"
    echo ""
    echo "WHAT'S EXCLUDED:"
    echo "  ✗ .env (create manually on server)"
    echo "  ✗ .git directory"
    echo "  ✗ logs, cache files"
    echo "  ✗ test files"
    echo ""
    echo "DEPLOYMENT STEPS:"
    echo "  1. Upload $ARCHIVE_NAME to cPanel File Manager"
    echo "  2. Extract to /home/brightbr/public_html/"
    echo "  3. Create .env file (use .env.production as template)"
    echo "  4. Set permissions:"
    echo "     chmod 755 storage/ -R"
    echo "     chmod 755 bootstrap/cache/ -R"
    echo "  5. Test: https://brightbridge.uz"
    echo ""
    echo "⚠️  NO NEED to run composer install!"
    echo "    Everything is ready to use."

elif [ "$PACKAGE_TYPE" = "terminal" ]; then
    echo "MODE: Terminal Optimized (without vendor/)"
    echo ""
    echo "WHAT'S INCLUDED:"
    echo "  ✓ All application code"
    echo "  ✓ composer.json and composer.lock"
    echo "  ✓ deploy_via_terminal.sh (automated setup script)"
    echo ""
    echo "WHAT'S EXCLUDED:"
    echo "  ✗ vendor/ (will be generated on server)"
    echo "  ✗ .env (create manually on server)"
    echo "  ✗ .git directory"
    echo "  ✗ logs, cache files"
    echo "  ✗ test files"
    echo ""
    echo "🎯 DEPLOYMENT STEPS (via cPanel Terminal):"
    echo ""
    echo "  1. Upload $ARCHIVE_NAME to cPanel File Manager"
    echo "  2. Extract to /home/brightbr/"
    echo "  3. Open cPanel → Advanced → Terminal"
    echo "  4. Run in Terminal:"
    echo ""
    echo "     cd ~/public_html"
    echo "     bash deploy_via_terminal.sh"
    echo ""
    echo "  5. Create/configure .env file (use .env.production as template)"
    echo "  6. Test: https://brightbridge.uz"
    echo ""
    echo "⚡ ADVANTAGES:"
    echo "  • Small upload size (~5-15MB)"
    echo "  • Fast upload"
    echo "  • Automated setup via script"
    echo "  • Fresh vendor/ generation on server"

else
    echo "MODE: Lite Package (without vendor/)"
    echo ""
    echo "WHAT'S INCLUDED:"
    echo "  ✓ All application code"
    echo "  ✓ composer.json and composer.lock"
    echo ""
    echo "WHAT'S EXCLUDED:"
    echo "  ✗ vendor/ (needs composer install on server)"
    echo "  ✗ .env (create manually on server)"
    echo "  ✗ .git directory"
    echo "  ✗ logs, cache files"
    echo "  ✗ test files"
    echo ""
    echo "DEPLOYMENT STEPS:"
    echo "  1. Upload $ARCHIVE_NAME to cPanel File Manager"
    echo "  2. Extract to /home/brightbr/public_html/"
    echo "  3. Create .env file (use .env.production as template)"
    echo "  4. Upload install.php"
    echo "  5. Visit: https://brightbridge.uz/install.php?key=deploy2025"
    echo "  6. Wait 2-3 minutes for composer install"
    echo "  7. Delete install.php"
    echo "  8. Test: https://brightbridge.uz"
    echo ""
    echo "⚠️  WARNING: This requires exec() to be enabled on server!"
    echo "    If install.php doesn't work, use 'terminal' mode instead."
fi

echo ""
echo "============================================================"
