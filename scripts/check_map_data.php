<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Rumah;

$all = Rumah::query()->count();
$approved = Rumah::query()->inApprovedBatch()->count();
$coordsAll = Rumah::query()->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude','!=',0)->where('longitude','!=',0)->count();
$coordsApproved = Rumah::query()->inApprovedBatch()->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude','!=',0)->where('longitude','!=',0)->count();

echo "all={$all} approved={$approved} coordsAll={$coordsAll} coordsApproved={$coordsApproved}\n";
