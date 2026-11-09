@echo off
setlocal
set "SIPANDU_PHP_DIR=%~dp0.runtime\php85"
set "SIPANDU_STORAGE_DIR=%~dp0storage"
set "PHPRC=%~dp0scripts\php.ini"
set "PHP_INI_SCAN_DIR="
set "PATH=%SIPANDU_PHP_DIR%;%PATH%"
if not exist "%SIPANDU_PHP_DIR%\php.exe" (
    echo PHP 8.5 belum tersedia. Jalankan scripts\setup-runtime.ps1 terlebih dahulu.
    exit /b 1
)
if not exist "%SIPANDU_STORAGE_DIR%\app\php-upload-tmp" mkdir "%SIPANDU_STORAGE_DIR%\app\php-upload-tmp"
"%SIPANDU_PHP_DIR%\php.exe" %*
exit /b %ERRORLEVEL%
