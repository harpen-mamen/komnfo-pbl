<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select(
    "SELECT TABLE_NAME, ENGINE, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'pbl' ORDER BY TABLE_NAME"
);

foreach ($rows as $row) {
    echo $row->TABLE_NAME . ': ' . ($row->ENGINE ?? 'NULL') . ' | ' . ($row->TABLE_COMMENT ?? '') . PHP_EOL;
}
