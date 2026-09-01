<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\User::with('roles')->get() as $user) {
    echo $user->email.' | roles='.$user->roles->pluck('name')->implode(',').' | filament='.(int) $user->canAccessFilament().PHP_EOL;
}
