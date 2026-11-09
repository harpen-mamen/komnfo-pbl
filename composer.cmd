@echo off
if not exist "%~dp0.runtime\composer.phar" (
    echo Composer lokal belum tersedia. Jalankan scripts\setup-runtime.ps1 terlebih dahulu.
    exit /b 1
)
call "%~dp0php.cmd" "%~dp0.runtime\composer.phar" %*
exit /b %ERRORLEVEL%
