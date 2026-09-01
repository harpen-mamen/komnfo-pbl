<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function runQuery($sql) {
    echo "-- SQL: $sql\n";
    try {
        $rows = DB::select($sql);
        foreach ($rows as $row) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        }
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . PHP_EOL;
    }
}

runQuery("SELECT TABLE_NAME, ENGINE, TABLE_ROWS, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA='pbl' AND TABLE_NAME='migrations'");
runQuery("SHOW CREATE TABLE migrations");
runQuery("SELECT COUNT(*) AS cnt FROM migrations");
