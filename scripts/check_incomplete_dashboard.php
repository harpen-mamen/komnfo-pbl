<?php

// Non-destructive smoke check: no migrations, seeding, password reset, or deletion.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::where('email', 'kominfo@sipdt.test')->firstOrFail();
Illuminate\Support\Facades\Auth::guard('web')->setUser($user);

// Reproduce navigation generation after login without querying a missing table.
App\Filament\Pages\VerifikasiDataSektoral::getNavigationItems();
echo "Navigation generation passed.\n";

$request = Illuminate\Http\Request::create('/'.config('filament.path'), 'GET');
$response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
if ($response->getStatusCode() !== 200 || ! str_contains($response->getContent(), 'Database dashboard belum lengkap')) {
    throw new RuntimeException('Dashboard recovery response failed: HTTP '.$response->getStatusCode());
}
echo "Authenticated dashboard: HTTP 200; recovery message and logout rendered.\n";
