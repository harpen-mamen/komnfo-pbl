@echo off
echo ============================================
echo   SIPDT Mobile - Development Server
echo ============================================
echo.

REM Check if Flutter is installed
where flutter >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Flutter not found. Please install Flutter SDK.
    pause
    exit /b 1
)

REM Navigate to parent directory (backend)
cd ..
echo [INFO] Backend directory: %CD%

REM Check if artisan exists
if not exist "artisan" (
    echo [ERROR] Laravel artisan not found. Make sure you run this from the correct directory.
    pause
    exit /b 1
)

REM Start Laravel backend server in new window
echo.
echo [1] Starting Laravel Backend Server...
echo    Server URL: http://localhost:8000
echo    API URL (for emulator): http://10.0.2.2:8000/api/v1
echo.
start "SIPDT Backend" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"

REM Wait a moment for server to start
timeout /t 3 /nobreak >nul

REM Go back to mobile directory
cd mobile

REM Start Flutter app
echo [2] Starting Flutter App...
echo.
flutter run

pause
