# Start Laravel dev server using the framework router from the project public folder
Push-Location (Split-Path -Parent $MyInvocation.MyCommand.Definition)
try {
    php artisan serve --host=127.0.0.1 --port=8000 --no-reload
} finally {
    Pop-Location
}
