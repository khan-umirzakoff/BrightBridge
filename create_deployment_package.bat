@echo off
REM BrightBridge Production Deployment Package Creator
REM Run: create_deployment_package.bat

echo ============================================================
echo  BrightBridge Production Deployment Package
echo ============================================================
echo.

cd public_html

REM 1. Clean logs
echo [1/5] Cleaning logs...
del /q storage\logs\*.log 2>nul
del /q storage\framework\cache\data\* 2>nul
del /q storage\framework\sessions\* 2>nul
del /q storage\framework\views\* 2>nul
echo     Done!

REM 2. Delete test files
echo [2/5] Removing test files...
del /q ..\test_*.php 2>nul
del /q ..\check_*.php 2>nul
del /q test_login.php 2>nul
del /q app\Http\Controllers\IndexController.php.broken 2>nul
echo     Done!

REM 3. Create .env.production template
echo [3/5] Creating .env.production template...
copy .env.example .env.production >nul
echo     Done!

REM 4. Create exclusion list for 7zip
echo [4/5] Creating ZIP (this may take 1-2 minutes)...

REM Create temp exclusion file
(
echo vendor\*
echo node_modules\*
echo .git\*
echo .env
echo storage\logs\*
echo storage\framework\cache\*
echo storage\framework\sessions\*
echo storage\framework\views\*
echo bootstrap\cache\*.php
echo *.backup
echo *test*.php
echo *check*.php
) > exclude.txt

REM ZIP with 7zip (if available) or use PowerShell
where 7z >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    7z a -tzip ..\brightbridge-deploy.zip . -xr@exclude.txt
) else (
    echo Using PowerShell compression...
    powershell -Command "Get-ChildItem -Recurse | Where-Object { $_.FullName -notmatch 'vendor|node_modules|\\.git|\\.env|storage\\logs|bootstrap\\cache.*\\.php|test.*\\.php|check.*\\.php' } | Compress-Archive -DestinationPath ..\brightbridge-deploy.zip -Force"
)

del exclude.txt
echo     Done!

echo [5/5] Package ready!
echo.
echo ============================================================
echo  DEPLOYMENT PACKAGE CREATED SUCCESSFULLY!
echo ============================================================
echo.
echo Location: D:\Desktop\BrightBridge\brightbridge-deploy.zip
echo Size:
dir ..\brightbridge-deploy.zip | find "brightbridge-deploy.zip"
echo.
echo WHAT'S EXCLUDED:
echo   - vendor/ (will run 'composer install' on server)
echo   - .env (create manually on server)
echo   - logs, cache files
echo   - test files
echo.
echo NEXT STEPS:
echo   1. Upload brightbridge-deploy.zip to cPanel
echo   2. Extract to public_html/
echo   3. Upload check_php_version.php
echo   4. Visit: https://brightbridge.uz/check_php_version.php
echo   5. Create .env file
echo   6. Run composer install (via install.php)
echo.
pause
