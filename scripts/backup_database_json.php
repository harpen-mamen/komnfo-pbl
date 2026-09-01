<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
$database = config('database.connections.mysql.database');
$key = 'Tables_in_'.$database;
$backup = ['database' => $database, 'created_at' => now()->toIso8601String(), 'tables' => []];

foreach ($tables as $table) {
    $name = $table->{$key};
    $backup['tables'][$name] = Illuminate\Support\Facades\DB::table($name)->get()->map(fn ($row) => (array) $row)->all();
}

$path = storage_path('backups/kominfo-before-dummy-cleanup.json');
if (! is_dir(dirname($path))) {
    mkdir(dirname($path), 0770, true);
}
file_put_contents($path, json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo $path.PHP_EOL;
