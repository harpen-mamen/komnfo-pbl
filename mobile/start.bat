@echo off
echo ============================================
echo   SIPDT Mobile - Development Launcher
echo ============================================
echo.

cd /d "%~dp0\.."

REM Check if PHP is available
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP not found. Please install PHP or add it to PATH.
    pause
    exit /b 1
)

REM Check if Laravel is ready
if not exist "artisan" (
    echo [ERROR] Laravel artisan not found. Are you in the correct directory?
    echo Expected: C:\pbl
    pause
    exit /b 1
)

REM Start backend server
echo.
echo [1/2] Starting Laravel Backend Server...
echo    URL: http://localhost:8000
echo    API: http://10.0.2.2:8000/api/v1 (for emulator)
echo.
start "SIPDT Backend" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"

echo Waiting for server to start...
timeout /t 3 /nobreak >nul

REM Start Flutter app
cd mobile
echo [2/2] Starting Flutter App...
flutter run

pause
