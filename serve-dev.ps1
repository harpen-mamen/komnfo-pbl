# Start Laravel dev server using the framework router from the project public folder
Push-Location (Split-Path -Parent $MyInvocation.MyCommand.Definition)
Push-Location public
php artisan serve --host=127.0.0.1 --port=8000
Pop-Location
Pop-Location
