@echo off
REM Start Laravel dev server from project public directory (Windows)
cd /d "%~dp0public"
php artisan serve --host=127.0.0.1 --port=8000
