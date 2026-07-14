<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Support\PanelAccess;
use Filament\Facades\Filament;

$email = 'admin.kecamatan@sipdt.test';
$password = 'password';

echo "Testing login for: $email\n";

$guard = Auth::guard(config('filament.auth.guard', 'web'));

$ok = $guard->attempt(['email' => $email, 'password' => $password]);

echo "Auth attempt result: ".($ok ? 'true' : 'false')."\n";

echo "Auth::check: ".(Auth::check() ? 'true' : 'false')."\n";

$user = Auth::user();
if ($user) {
    echo "User ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "kecamatan_id: ".($user->kecamatan_id ?? 'null')."\n";
    echo "Roles: ".json_encode($user->roles->pluck('name')) ."\n";
    echo "canAccessFilament (User::canAccessFilament): ".(method_exists($user, 'canAccessFilament') ? ($user->canAccessFilament() ? 'true':'false') : 'na')."\n";
    echo "PanelAccess::isAdminKecamatan: ".(PanelAccess::isAdminKecamatan() ? 'true' : 'false')."\n";
    try {
        echo "Filament::auth()->check: ".(Filament::auth()->check() ? 'true' : 'false')."\n";
    } catch (Throwable $e) {
        echo "Filament::auth()->check threw: ".get_class($e)." - ".$e->getMessage()."\n";
    }
}
else {
    echo "No user after attempt.\n";
}

// Show some relevant config
echo "Filament auth guard: " . config('filament.auth.guard') . "\n";
echo "Filament home_url: " . config('filament.home_url') . "\n";
echo "Session driver: " . config('session.driver') . "\n";
echo "Session domain: " . config('session.domain') . "\n";
