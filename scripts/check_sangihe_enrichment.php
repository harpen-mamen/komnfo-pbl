<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!in_array('--active', $argv, true)) {
    $settings = config('database.connections.mysql');
    $settings['database'] = 'kominfo_recovery_preview_20261025'; $settings['url'] = null;
    config(['database.connections.recovery_preview'=>$settings]);
    Illuminate\Support\Facades\DB::setDefaultConnection('recovery_preview');
}
$failures=[]; $checked=0;
$tables=require database_path('seeders/data/SangiheSectoral2024Supplement.php');
foreach ($tables as $code=>$table) {
    $dataset=App\Models\Dataset::with('fields')->where('kode',(string)$code)->firstOrFail();
    foreach ($table['rows'] as $row) {
        $key=implode('|',array_map(fn($name)=>(string)($row[$name]??''),$table['key']));
        $marker='[SANGIHE-SOURCE:'.substr(hash('sha256','2024-'.$code.'-'.$key),0,24).']';
        $records=$dataset->values()->where('notes','like',$marker.'%')->get();
        if ($records->count()!==1 || $records->first()->values != $row || $records->first()->status!=='published') $failures[]='Source row mismatch '.$code.' '.$key;
        $checked++;
    }
    app(App\Services\DynamicChartService::class)->statisticsForDataset($dataset);
    $export=app(App\Services\DataInput\DatasetExportService::class);
    $export->toCsv($dataset,'published'); $export->toExcelHtml($dataset,'published');
}
$spatial=App\Models\Dataset::with('fields','opd')->whereIn('kode',['LOKASI_TRANSPORTASI_PUBLIK','LOKASI_FASKES_PUBLIK'])->get();
$features=[];
foreach ($spatial as $dataset) {
    $features=array_merge($features,app(App\Services\DatasetSpatialDataService::class)->featuresForDataset($dataset));
    app(App\Services\DataInput\DatasetExportService::class)->toCsv($dataset,'published');
    app(App\Services\DataInput\DatasetExportService::class)->toExcelHtml($dataset,'published');
}
if (count($features)!==4) $failures[]='Expected exactly four sourced map points';
foreach ($features as $feature) if (empty($feature['properties']['source_url'])) $failures[]='Missing point provenance';
$rejected=App\Models\DatasetValue::whereHas('dataset',fn($q)=>$q->where('kode','LOKASI_FASKES_PUBLIK'))->get()->filter(fn($v)=>str_starts_with($v->values['status_lokasi']??'','Koordinat sumber ditolak'));
foreach ($rejected as $record) if (isset($record->values['geometri']) || isset($record->values['latitude']) || isset($record->values['longitude'])) $failures[]='Rejected coordinate leaked';
echo json_encode(['database'=>Illuminate\Support\Facades\DB::connection()->getDatabaseName(),'datasets'=>App\Models\Dataset::count(),'values'=>App\Models\DatasetValue::count(),'source_tables'=>count($tables),'source_rows_checked'=>$checked,'map_points'=>count($features),'facilities_without_coordinates'=>$rejected->count(),'errors'=>$failures],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($failures?1:0);
