@echo off
title SIPDT Mobile - Quick Launcher
color 0A

echo.
echo  *********************************************
echo  *   SIPDT MOBILE - QUICK LAUNCHER         *
echo  *   Pendataan Terpadu Kab. Kepulauan Sangihe*
echo  *********************************************
echo.

REM Get IP Address
for /f "tokens=1 delims= " %%a in ('ipconfig ^| findstr /i "IPv4" ^| findstr /v "127"') do set IPADDR=%%a
echo [INFO] IP Komputer Anda: %IPADDR%
echo.

REM Check if backend is running
netstat -an | findstr ":8000" | findstr "LISTENING" >nul
if %errorlevel% neq 0 (
    echo [WARNING] Backend tidak berjalan!
    echo.
    set /p START_BACKEND="Mau jalankan backend sekarang? (y/n): "
    if /i "%START_BACKEND%"=="y" (
        echo.
        echo [INFO] Menjalankan Backend...
        cd /d "%~dp0\.."
        start "SIPDT Backend" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
        echo [INFO] Menunggu server...
        timeout /t 3 >nul
    )
) else (
    echo [OK] Backend sudah berjalan!
)

echo.
echo ============================================
echo   PANDUAN PENGGUNAAN
echo ============================================
echo.
echo 1. Pastikan HP dan komputer terhubung LAN/WiFi yang sama
echo.
echo 2. Untuk Android Emulator:
echo    flutter run
echo.
echo 3. Untuk HP Fisik via USB:
echo    flutter run --dart-define=SIPDT_API_BASE_URL=http://%IPADDR%:8000/api/v1
echo.
echo 4. Untuk HP Fisik via WiFi (same network):
echo    flutter run --dart-define=SIPDT_API_BASE_URL=http://%IPADDR%:8000/api/v1
echo.
echo ============================================
echo   KREDENSIAL LOGIN
echo ============================================
echo.
echo    Email:    admin@sipdt.sangihe.go.id
echo    Password: password123
echo.
echo ============================================
echo.

set /p LAUNCH="Tekan ENTER untuk jalankan Flutter..."

REM Run Flutter with IP address
cd /d "%~dp0"
flutter run --dart-define=SIPDT_API_BASE_URL=http://%IPADDR%:8000/api/v1

pause
