# Laravel Project Setup for Laragon

## Problem
The admin panel assets are not loading because the local server document root is not configured properly for Laravel.

## Solution: Configure Virtual Host in Laragon

### Step 1: Stop Laragon Services
1. Open Laragon
2. Click "Stop All"

### Step 2: Create Virtual Host
1. Right-click Laragon icon → Apache → sites-enabled → "Open"
2. Create new file: `brightbridge.local.conf`
3. Copy content from `brightbridge.local.conf` file in this directory
4. Save the file

### Step 3: Add to Hosts File
1. Open `C:\Windows\System32\drivers\etc\hosts` as Administrator
2. Add this line:
   ```
   127.0.0.1    brightbridge.local
   ```

### Step 4: Restart Laragon
1. Start Laragon services
2. Access your project at: `http://brightbridge.local/admin/site`

### Alternative Solution: Use Laravel's Built-in Server
If virtual host configuration is complex, you can use Laravel's built-in server:

1. Open terminal in project directory
2. Run: `php artisan serve --port=8000`
3. Access: `http://localhost:8000/admin/site`

## Why This Fixes the Issue
- Production server: Document root is `/public_html/public/`
- Local server (current): Document root is `/public_html/`
- Local server (fixed): Document root is `/public_html/public/`

This ensures your local environment matches production exactly.

## Admin Login Credentials
- URL: `http://brightbridge.local/company-login`
- Email: `admin@local.dev`
- Password: `admin123`