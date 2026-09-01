@echo off
REM Dashboard Analytics Setup Script for Windows
REM Run this script to setup the dashboard analytics system
REM Usage: setup-dashboard.bat

echo.
echo ================================
echo Dashboard Analytics Setup
echo ================================
echo.

REM Check if composer is installed
where composer >nul 2>nul
if errorlevel 1 (
    echo Error: Composer is not installed. Please install Composer first.
    pause
    exit /b 1
)

REM Check if npm is installed
where npm >nul 2>nul
if errorlevel 1 (
    echo Error: NPM is not installed. Please install Node.js and NPM first.
    pause
    exit /b 1
)

echo Prerequisites check passed
echo.

REM Step 1: Install NPM dependencies
echo Step 1: Installing NPM dependencies...
call npm install apexcharts chart.js leaflet leaflet.markercluster
if errorlevel 1 (
    echo Error installing NPM dependencies
    pause
    exit /b 1
)
echo NPM dependencies installed
echo.

REM Step 2: Build assets
echo Step 2: Building assets...
call npm run build
if errorlevel 1 (
    echo Error building assets
    pause
    exit /b 1
)
echo Assets built successfully
echo.

REM Step 3: Clear caches
echo Step 3: Clearing caches...
call php artisan optimize:clear
call php artisan cache:clear
echo Caches cleared
echo.

REM Step 4: Publish Filament assets
echo Step 4: Publishing Filament assets...
call php artisan filament:install
call php artisan vendor:publish --tag=filament-config --force
if errorlevel 1 (
    echo Warning: Some Filament assets may not have published
)
echo Filament assets published
echo.

REM Step 5: Optimize Laravel
echo Step 5: Optimizing Laravel...
call php artisan optimize
echo Laravel optimized
echo.

echo ================================
echo Setup Completed Successfully!
echo ================================
echo.
echo Next Steps:
echo 1. Add export routes to routes/web.php
echo 2. Register CSS in your blade layout
echo 3. Run: php artisan serve
echo 4. Visit: http://localhost:8000/admin
echo.
echo Documentation:
echo    - DASHBOARD_DOCUMENTATION.md
echo    - IMPLEMENTATION_CHECKLIST.md
echo    - EXPORT_ROUTES_SETUP.md
echo.
pause
