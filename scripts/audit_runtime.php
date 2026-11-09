<?php

$started = microtime(true);
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'bootstrap_ms='.round((microtime(true) - $started) * 1000).PHP_EOL;
$db = Illuminate\Support\Facades\DB::connection();
$database = $db->getDatabaseName();
$tables = $db->select('SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME', [$database]);
echo json_encode(['database' => $database, 'tables' => $tables], JSON_PRETTY_PRINT).PHP_EOL;
echo json_encode(['foreign_keys' => $db->select('SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL', [$database])], JSON_PRETTY_PRINT).PHP_EOL;
foreach (['dataset_values', 'datasets', 'dataset_fields'] as $table) {
    if (Illuminate\Support\Facades\Schema::hasTable($table)) {
        echo $table.' indexes: '.json_encode($db->select('SHOW INDEX FROM `'.$table.'`')).PHP_EOL;
    }
}
