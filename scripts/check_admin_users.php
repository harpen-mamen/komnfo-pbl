<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$emails = ['admin@sipdt.test', 'admin.kecamatan@sipdt.test'];
$users = User::whereIn('email', $emails)->get();

$total = User::count();
echo "total users: $total\n";

if ($users->isEmpty()) {
    echo "No seeded admin users found.\n";
} else {
    foreach ($users as $user) {
        echo 'email: ' . $user->email . "\n";
        echo 'id: ' . $user->id . "\n";
        echo 'roles: ' . $user->roles->pluck('name')->join(', ') . "\n";
        echo 'password hash: ' . $user->password . "\n";
        echo 'kecamatan_id: ' . ($user->kecamatan_id ?? 'null') . "\n";
        echo "---\n";
    }
}

foreach ($users as $user) {
    echo 'email: ' . $user->email . "\n";
    echo 'id: ' . $user->id . "\n";
    echo 'roles: ' . $user->roles->pluck('name')->join(', ') . "\n";
    echo 'password hash: ' . $user->password . "\n";
    echo 'kecamatan_id: ' . ($user->kecamatan_id ?? 'null') . "\n";
    echo "---\n";
}

echo 'count: ' . $users->count() . "\n";
