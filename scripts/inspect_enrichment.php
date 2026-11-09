<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Auth::guard('web')->setUser(App\Models\User::where('email', 'kominfo@sipdt.test')->firstOrFail());
$codes = ['3.20','3.21','3.29','3.30','3.31','3.32','4.41','4.42','4.43','4.44','4.46','4.47','4.48','5.49','5.50','5.54','5.55','5.57','5.58','9.88','9.92'];
foreach (App\Models\Dataset::with('fields','opd')->withCount('values')->whereIn('kode', $codes)->get() as $dataset) {
    echo json_encode(['code'=>$dataset->kode,'name'=>$dataset->nama,'opd'=>$dataset->opd->kode,'count'=>$dataset->values_count,'fields'=>$dataset->fields->map(fn($field)=>[$field->nama,$field->tipe,$field->options,$field->lookup_source])->all()], JSON_UNESCAPED_UNICODE).PHP_EOL;
}
foreach (App\Models\Dataset::with('fields','opd')->where('kode','not like','%.%')->whereHas('fields',fn($q)=>$q->where('lookup_source','like','geometry:%'))->get() as $dataset) {
    echo json_encode(['spatial_code'=>$dataset->kode,'opd'=>$dataset->opd->kode,'fields'=>$dataset->fields->map(fn($field)=>[$field->nama,$field->tipe,$field->options,$field->lookup_source])->all()], JSON_UNESCAPED_UNICODE).PHP_EOL;
}
foreach (Filament\Facades\Filament::getNavigation() as $group) {
    foreach ($group->getItems() as $item) {
        echo 'NAV '.json_encode([$item->getLabel(),$item->getUrl()], JSON_UNESCAPED_UNICODE).PHP_EOL;
    }
}
