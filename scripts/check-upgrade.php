<?php

// Read-only database smoke check; temporary Redis keys and XLSX files are cleaned up.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

$results = ['php' => PHP_VERSION, 'laravel' => app()->version()];
foreach (['filament/filament', 'livewire/livewire', 'predis/predis', 'maatwebsite/excel'] as $package) {
    $results[$package] = InstalledVersions::getPrettyVersion($package);
}
$failed = false;
foreach (['gd', 'intl', 'mbstring', 'pdo_mysql', 'pdo_sqlite', 'zip'] as $extension) {
    $results['extensions'][$extension] = extension_loaded($extension);
    $failed = $failed || ! $results['extensions'][$extension];
}

try {
    $results['database'] = (int) DB::selectOne('SELECT 1 AS ok')->ok === 1 ? 'ok' : 'failed';
    $info = Redis::connection()->info('server');
    $results['redis_server'] = data_get($info, 'Server.redis_version', data_get($info, 'redis_version', 'connected'));
    if (config('cache.default') !== 'redis') {
        throw new RuntimeException('The application cache must use Redis.');
    }

    $key = 'upgrade-smoke:'.bin2hex(random_bytes(12));
    $lock = Cache::lock($key.':lock', 10);
    try {
        Cache::put($key, ['ok' => true], 60);
        $ttl = Cache::getStore()->connection()->ttl(Cache::getStore()->getPrefix().$key);
        if (Cache::get($key) !== ['ok' => true] || $ttl < 1 || $ttl > 60 || ! $lock->get()) {
            throw new RuntimeException('Redis cache, TTL, or lock failed.');
        }
        $results['redis_cache_ttl_lock'] = 'ok';
    } finally {
        $lock->release();
        Cache::forget($key);
    }

    $export = new class implements FromArray {
        public function array(): array
        {
            return [['Indikator', 'Nilai', 'Satuan'], ['Panjang jalan', 12.5, 'km']];
        }
    };
    $xlsx = app(Excel::class)->raw($export, Excel::XLSX);
    $temporaryFile = tempnam(sys_get_temp_dir(), 'sipandu-upgrade-');
    try {
        file_put_contents($temporaryFile, $xlsx);
        $spreadsheet = IOFactory::createReader('Xlsx')->load($temporaryFile);
        if ($spreadsheet->getActiveSheet()->getCell('B2')->getValue() !== 12.5) {
            throw new RuntimeException('Excel round-trip changed the source value.');
        }
        $spreadsheet->disconnectWorksheets();
        $results['xlsx_round_trip'] = 'ok';
    } finally {
        if (is_file($temporaryFile)) {
            unlink($temporaryFile);
        }
    }
} catch (Throwable $exception) {
    $results['error'] = $exception->getMessage();
    $failed = true;
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failed ? 1 : 0);
