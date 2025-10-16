#!/bin/bash
# BrightBridge - Complete Server Setup Script
# This script can be run directly on the server via cPanel Terminal
# Usage: bash server_setup.sh

set -e  # Exit on error

echo "============================================================"
echo "  BrightBridge Production Setup"
echo "============================================================"
echo ""

# Configuration
REPO_URL="https://github.com/khan-umirzakoff/BrightBridge.git"
BRANCH="feature/fix-environment-setup"  # Current working branch
TARGET_DIR="$HOME/public_html"
BACKUP_DIR="$HOME/backup_$(date +%Y%m%d_%H%M%S)"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Configuration:${NC}"
echo "  Repository: $REPO_URL"
echo "  Branch: $BRANCH"
echo "  Target: $TARGET_DIR"
echo ""

# Step 1: Check if target directory exists
echo -e "${YELLOW}[1/10] Checking existing installation...${NC}"
if [ -d "$TARGET_DIR" ] && [ "$(ls -A $TARGET_DIR)" ]; then
    echo "  ⚠️  Existing files found in $TARGET_DIR"
    read -p "  Do you want to backup existing files? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "  Creating backup at $BACKUP_DIR..."
        mkdir -p "$BACKUP_DIR"
        cp -r "$TARGET_DIR"/* "$BACKUP_DIR/" 2>/dev/null || true
        echo "  ✅ Backup created"
    fi

    # Backup .env if exists
    if [ -f "$TARGET_DIR/.env" ]; then
        echo "  Backing up .env file..."
        cp "$TARGET_DIR/.env" "$HOME/.env.backup.$(date +%Y%m%d_%H%M%S)"
        echo "  ✅ .env backed up to home directory"
    fi
else
    echo "  ✅ Target directory is empty or doesn't exist"
fi
echo ""

# Step 2: Clone or pull repository
echo -e "${YELLOW}[2/10] Fetching latest code...${NC}"
TEMP_CLONE_DIR="$HOME/temp_brightbridge_clone"

if [ -d "$TEMP_CLONE_DIR" ]; then
    rm -rf "$TEMP_CLONE_DIR"
fi

echo "  Cloning repository..."
git clone --branch "$BRANCH" "$REPO_URL" "$TEMP_CLONE_DIR"
echo "  ✅ Repository cloned"
echo ""

# Step 3: Copy files to target directory
echo -e "${YELLOW}[3/10] Deploying files...${NC}"
mkdir -p "$TARGET_DIR"

# Copy public_html contents to target
if [ -d "$TEMP_CLONE_DIR/public_html" ]; then
    echo "  Copying files from public_html/..."
    cp -rf "$TEMP_CLONE_DIR/public_html"/* "$TARGET_DIR/"
    echo "  ✅ Files deployed"
else
    echo "  ❌ Error: public_html directory not found in repository"
    exit 1
fi
echo ""

# Step 4: Change to target directory
cd "$TARGET_DIR"
echo -e "${YELLOW}[4/10] Working directory: $(pwd)${NC}"
echo ""

# Step 5: Check PHP version
echo -e "${YELLOW}[5/10] Checking environment...${NC}"
PHP_VERSION=$(php -v | head -n 1)
echo "  PHP: $PHP_VERSION"
echo "  Git: $(git --version)"
echo "  ✅ Environment check complete"
echo ""

# Step 6: Setup Composer
echo -e "${YELLOW}[6/10] Setting up Composer...${NC}"
if [ -f "composer.phar" ]; then
    echo "  ✅ composer.phar found"
    COMPOSER_CMD="php composer.phar"
elif command -v composer &> /dev/null; then
    echo "  ✅ composer command found"
    COMPOSER_CMD="composer"
else
    echo "  Downloading composer.phar..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    php -r "unlink('composer-setup.php');"
    echo "  ✅ composer.phar downloaded"
    COMPOSER_CMD="php composer.phar"
fi
echo ""

# Step 7: Install dependencies
echo -e "${YELLOW}[7/10] Installing dependencies (this may take 2-3 minutes)...${NC}"
$COMPOSER_CMD install --no-dev --optimize-autoloader --no-interaction
echo "  ✅ Dependencies installed"
echo ""

# Step 8: Set permissions
echo -e "${YELLOW}[8/10] Setting permissions...${NC}"
chmod -R 755 storage/ 2>/dev/null || true
chmod -R 755 bootstrap/cache/ 2>/dev/null || true

# Ensure directories exist
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

echo "  ✅ Permissions set"
echo ""

# Step 9: Laravel optimization
echo -e "${YELLOW}[9/10] Optimizing Laravel...${NC}"

# Clear caches first
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Cache for production
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo "  ✅ Laravel optimized"
echo ""

# Step 10: Verify installation
echo -e "${YELLOW}[10/10] Verifying installation...${NC}"
echo ""

# Check Laravel
if [ -f "artisan" ]; then
    LARAVEL_VERSION=$(php artisan --version 2>/dev/null || echo "Unknown")
    echo "  Laravel: $LARAVEL_VERSION"
else
    echo "  ❌ artisan not found!"
fi

# Check vendor
if [ -f "vendor/autoload.php" ]; then
    echo "  ✅ vendor/autoload.php exists"
else
    echo "  ❌ vendor/autoload.php missing!"
fi

# Check .env
if [ -f ".env" ]; then
    echo "  ✅ .env file exists"
else
    echo -e "  ${RED}⚠️  .env file NOT found${NC}"
    if [ -f ".env.production" ]; then
        echo ""
        read -p "  Create .env from .env.production template? (y/n): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            cp .env.production .env
            echo "  ✅ .env created from template"
            echo -e "  ${YELLOW}⚠️  IMPORTANT: Edit .env and configure:${NC}"
            echo "     - Database credentials"
            echo "     - API keys (GEMINI_API_KEY, OPENAI_API_KEY)"
            echo "     - APP_KEY (run: php artisan key:generate)"
        fi
    fi
fi

# Check storage
if [ -d "storage/logs" ]; then
    echo "  ✅ storage/logs directory exists"
else
    echo "  ❌ storage/logs directory missing!"
fi

# Cleanup
echo ""
echo -e "${YELLOW}Cleaning up temporary files...${NC}"
rm -rf "$TEMP_CLONE_DIR"
echo "  ✅ Cleanup complete"

echo ""
echo "============================================================"
echo -e "  ${GREEN}DEPLOYMENT COMPLETED!${NC}"
echo "============================================================"
echo ""
echo -e "${GREEN}✅ Code deployed${NC}"
echo -e "${GREEN}✅ Dependencies installed${NC}"
echo -e "${GREEN}✅ Permissions set${NC}"
echo -e "${GREEN}✅ Laravel optimized${NC}"
echo ""

# Check if .env needs configuration
if [ ! -f ".env" ] || ! grep -q "DB_PASSWORD=." ".env" 2>/dev/null; then
    echo -e "${YELLOW}⚠️  IMPORTANT NEXT STEPS:${NC}"
    echo ""
    echo "1. Configure .env file:"
    echo "   nano .env"
    echo ""
    echo "   Update these values:"
    echo "   - DB_DATABASE=brightbr_job"
    echo "   - DB_USERNAME=your_db_user"
    echo "   - DB_PASSWORD=your_db_password"
    echo "   - GEMINI_API_KEY=your_gemini_key"
    echo "   - OPENAI_API_KEY=your_openai_key"
    echo ""
    echo "2. Generate application key:"
    echo "   php artisan key:generate"
    echo ""
    echo "3. Clear cache after .env changes:"
    echo "   php artisan config:clear"
    echo "   php artisan config:cache"
    echo ""
fi

echo "4. Test your application:"
echo "   https://brightbridge.uz"
echo "   https://brightbridge.uz/admin/site"
echo ""
echo "5. Check logs for errors:"
echo "   tail -f storage/logs/laravel.log"
echo ""

if [ -d "$BACKUP_DIR" ]; then
    echo "📦 Backup location: $BACKUP_DIR"
    echo ""
fi

echo "============================================================"
echo ""
echo -e "${GREEN}🎉 Setup complete! Happy coding!${NC}"
echo ""
