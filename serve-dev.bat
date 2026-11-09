@echo off
REM Start Laravel dev server from the project root (Windows)
cd /d "%~dp0"
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
