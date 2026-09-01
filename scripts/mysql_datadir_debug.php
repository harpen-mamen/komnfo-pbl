<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function out($msg) {
    echo $msg . PHP_EOL;
}

$out = DB::select("SELECT @@datadir AS datadir");
if ($out) {
    out('datadir=' . $out[0]->datadir);
}
$out = DB::select("SHOW VARIABLES LIKE 'innodb_data_file_path'");
foreach ($out as $row) {
    out($row->Variable_name . '=' . $row->Value);
}
$out = DB::select("SHOW VARIABLES LIKE 'datadir'");
foreach ($out as $row) {
    out($row->Variable_name . '=' . $row->Value);
}
$out = DB::select("SELECT TABLE_NAME, ENGINE, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA='pbl' ORDER BY TABLE_NAME");
foreach ($out as $row) {
    out($row->TABLE_NAME . ': ' . ($row->ENGINE ?? 'NULL') . ' | ' . ($row->TABLE_COMMENT ?? ''));
}
