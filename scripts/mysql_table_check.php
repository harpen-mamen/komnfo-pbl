<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function out($msg) {
    echo $msg . PHP_EOL;
}

$tables = [
    'migrations',
    'users',
    'submission_batches',
];

foreach ($tables as $table) {
    out("--- CHECK TABLE $table ---");
    try {
        $results = DB::select("CHECK TABLE `$table`");
        foreach ($results as $row) {
            $fields = get_object_vars($row);
            out(implode(' | ', $fields));
        }
    } catch (Exception $e) {
        out('CHECK TABLE ERROR: ' . $e->getMessage());
    }
}

out('--- SHOW CREATE TABLE migrations ---');
try {
    $row = DB::select('SHOW CREATE TABLE migrations');
    if ($row) {
        $obj = (array)$row[0];
        out($obj['Create Table'] ?? $obj['Create Table']);
    }
} catch (Exception $e) {
    out('SHOW CREATE TABLE ERROR: ' . $e->getMessage());
}

out('--- SELECT COUNT(*) FROM migrations ---');
try {
    $count = DB::table('migrations')->count();
    out('count=' . $count);
} catch (Exception $e) {
    out('COUNT ERROR: ' . $e->getMessage());
}

out('--- INFORMATION_SCHEMA FOR migrations ---');
try {
    $info = DB::select("SELECT TABLE_NAME, ENGINE, TABLE_ROWS, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'migrations'");
    foreach ($info as $row) {
        $fields = get_object_vars($row);
        out(implode(' | ', $fields));
    }
} catch (Exception $e) {
    out('INFOSCHEMA ERROR: ' . $e->getMessage());
}
