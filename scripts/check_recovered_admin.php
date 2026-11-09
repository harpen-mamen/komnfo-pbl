<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! in_array('--active', $argv, true)) {
    $config = config('database.connections.mysql');
    $config['database'] = 'kominfo_recovery_preview_20261025';
    $config['url'] = null;
    config(['database.connections.recovery_preview' => $config]);
    Illuminate\Support\Facades\DB::setDefaultConnection('recovery_preview');
}
$user = App\Models\User::where('email', 'kominfo@sipdt.test')->firstOrFail();
Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
$opdId = App\Models\Dataset::whereHas('values')->value('opd_id');
$recordId = App\Models\DatasetValue::whereHas('dataset', fn ($query) => $query->where('kode', '3.13'))->value('id');
$paths = ['/admin', '/admin/data-opd', '/admin/opds', '/admin/dataset-values', '/admin/dataset-values?dataset=3.13', '/admin/dataset-values/create?dataset=3.13', '/admin/dataset-values/'.$recordId.'?dataset=3.13', '/admin/verifikasi-data-sektoral', '/admin/pengaturan-profil', '/admin/executive-dashboard', '/admin/peta-pintar', '/admin/data-opd/'.$opdId, '/admin/infografis-opd/'.$opdId];
$failed = false;
foreach ($paths as $path) {
    $start = microtime(true);
    $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create($path, 'GET'));
    echo $path.' HTTP '.$response->getStatusCode().' '.round((microtime(true) - $start) * 1000).'ms'.PHP_EOL;
    $expected = str_contains($path, '/create?') ? 403 : 200;
    if ($response->getStatusCode() !== $expected) {
        $failed = true;
        $html = $response->getContent();
        preg_match('/<title>(.*?)<\/title>/s', $html, $match);
        echo html_entity_decode(strip_tags($match[1] ?? 'Unknown error')).PHP_EOL;
    }
}
$operator = clone $user;
$operator->opd_id = App\Models\Dataset::where('kode', '3.13')->value('opd_id');
$operator->setRelation('roles', new Illuminate\Database\Eloquent\Collection([
    new Spatie\Permission\Models\Role(['name' => 'admin_opd', 'guard_name' => 'web']),
]));
Illuminate\Support\Facades\Auth::guard('web')->setUser($operator);
$response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create('/admin/dataset-values/create?dataset=3.13', 'GET'));
echo 'Own-OPD input form HTTP '.$response->getStatusCode().PHP_EOL;
if ($response->getStatusCode() !== 200) {
    $failed = true;
    preg_match('/<title>(.*?)<\/title>/s', $response->getContent(), $match);
    echo html_entity_decode(strip_tags($match[1] ?? 'Unknown error')).PHP_EOL;
}
exit($failed ? 1 : 0);
