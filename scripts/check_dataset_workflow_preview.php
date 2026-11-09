<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$config = config('database.connections.mysql');
$config['database'] = 'kominfo_recovery_preview_20261025';
$config['url'] = null;
config(['database.connections.recovery_preview' => $config]);
Illuminate\Support\Facades\DB::setDefaultConnection('recovery_preview');
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDatabaseName() !== 'kominfo_recovery_preview_20261025') {
    throw new RuntimeException('Workflow smoke test requires the isolated preview.');
}
$db->beginTransaction();
try {
    $kominfo = App\Models\User::where('email', 'kominfo@sipdt.test')->firstOrFail();
    $dataset = App\Models\Dataset::with('fields')->where('kode', '3.13')->firstOrFail();
    $operator = clone $kominfo;
    $operator->opd_id = $dataset->opd_id;
    $operator->setRelation('roles', new Illuminate\Database\Eloquent\Collection([
        new Spatie\Permission\Models\Role(['name' => 'admin_opd', 'guard_name' => 'web']),
    ]));
    Illuminate\Support\Facades\Auth::guard('web')->setUser($operator);
    $manager = app(App\Services\DataInput\DatasetValueManager::class);
    $data = ['tahun' => 2024, 'periode_label' => 'SMOKE-ROLLBACK', 'values' => [
        'kecamatan' => App\Models\Wilayah::where('tipe', 'kecamatan')->value('id'),
        'penduduk_laki' => 1, 'penduduk_perempuan' => 1, 'total' => 2,
    ]];
    $value = $manager->createDraft($dataset, $data);
    $manager->updateDraft($value, $data + ['dataset_id' => $dataset->id]);
    $manager->submitMany([$value->id], $operator);
    $value->refresh();
    Illuminate\Support\Facades\Auth::guard('web')->setUser($kominfo);
    $manager->verify($value, 'SMOKE-ROLLBACK', false);
    $manager->publish($value);
    if ($value->fresh()->status !== 'published' || $db->table('workflow_logs')->where('data_value_id', $value->id)->count() !== 3) {
        throw new RuntimeException('Workflow status or audit trail mismatch.');
    }
    echo 'PASS create -> update -> submit -> verify -> publish; 3 audit logs. All test rows rolled back.'.PHP_EOL;
} finally {
    $db->rollBack();
}
