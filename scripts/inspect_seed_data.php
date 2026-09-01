<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['users', 'submission_batches', 'rumah', 'keluarga', 'penduduk', 'data_kesehatan_bulanans', 'data_pendidikan_bulanans', 'kecamatans', 'desas'] as $table) {
    echo $table.'='.Illuminate\Support\Facades\DB::table($table)->count().PHP_EOL;
}

echo "USERS".PHP_EOL;
foreach (App\Models\User::query()->select('id', 'email', 'name', 'desa_id', 'kecamatan_id')->get() as $user) {
    echo implode('|', [$user->id, $user->email, $user->name, $user->desa_id, $user->kecamatan_id]).PHP_EOL;
}

echo "DUMMY_BATCHES".PHP_EOL;
foreach (App\Models\SubmissionBatch::query()->where('notes', 'like', '%dummy%')->orWhere('submission_number', 'like', 'DUMMY-%')->get(['id', 'submission_number', 'module', 'status', 'created_by']) as $batch) {
    echo implode('|', [$batch->id, $batch->submission_number, $batch->module, $batch->status, $batch->created_by]).PHP_EOL;
}
