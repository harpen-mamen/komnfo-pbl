<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = User::with('roles')->get()->map(function($u){
    return [
        'id' => $u->id,
        'email' => $u->email,
        'kecamatan_id' => $u->kecamatan_id,
        'roles' => $u->roles->pluck('name')->toArray(),
    ];
});

echo json_encode($users->toArray(), JSON_PRETTY_PRINT);
