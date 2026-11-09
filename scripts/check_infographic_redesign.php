<?php

require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user=App\Models\User::where('email','kominfo@sipdt.test')->firstOrFail();
Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
$errors=[]; $responses=0;
$dataset=App\Models\Dataset::with('fields','values')->where('kode','5.59')->firstOrFail();
$charts=app(App\Services\DynamicChartService::class)->statisticsForDataset($dataset,$dataset->values);
if (array_column($charts,'unit')!==['km','km','km','unit','m']) $errors[]='Road/bridge units mixed';
if (array_column($charts,'values')!==[[379.97,58.28,171.81],[293.6,2.59,53.24,30.54],[190.81,58.47,31.75,98.94],[170.0],[1714.0]]) $errors[]='Road source values changed/grouped incorrectly';
$page=app(App\Filament\Pages\InfografisOpd::class);
$page->mount((string)$dataset->opd_id);
$allCount=count($page->visualDatasets);
$page->selectedYear='2024'; $page->updatedSelectedYear();
if (count($page->visualDatasets)!==$allCount) $errors[]='Year filter lost 2024 PUPR datasets';
foreach ($page->visualDatasets as $item) if (array_map('intval',$item['context']['years'])!==[2024]) $errors[]='Year filter mixed years';
$paths=['/admin/infografis-opd/'.$dataset->opd_id,'/admin/infografis-dataset/5.59','/admin/infografis-dataset/5.57','/admin/infografis-dataset/5.58'];
if (in_array('--all',$argv,true)) {
    foreach (App\Models\Opd::where('aktif',true)->pluck('id') as $id) $paths[]='/admin/infografis-opd/'.$id;
    foreach (App\Models\Dataset::where('is_active',true)->pluck('kode') as $code) $paths[]='/admin/infografis-dataset/'.urlencode($code);
}
foreach (array_unique($paths) as $path) {
    $response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create($path,'GET'));
    $responses++;
    if ($response->getStatusCode()!==200) {
        preg_match('/<title>(.*?)<\/title>/s',$response->getContent(),$title);
        $error=[$path,$response->getStatusCode(),html_entity_decode(strip_tags($title[1]??''))];
        $errors[]=$error; echo json_encode($error).PHP_EOL;
    }
    if (str_ends_with($path,'/5.59') || $path==='/admin/infografis-opd/'.$dataset->opd_id) {
        foreach (['Jumlah jembatan','Panjang keseluruhan jembatan','379,97','1.714','170','Lihat tabel angka'] as $text) if (!str_contains($response->getContent(),$text)) $errors[]='Missing visible meaning/value: '.$text;
    }
    if ($responses%20===0) echo 'Checked '.$responses.' infographic pages'.PHP_EOL;
}
$result=['database'=>Illuminate\Support\Facades\DB::connection()->getDatabaseName(),'responses'=>$responses,'road_charts'=>count($charts),'year_filter'=>'2024 checked','errors'=>$errors];
Illuminate\Support\Facades\File::put(storage_path('app/source-imports/2026-10-25/infographic-redesign-audit'.(in_array('--all',$argv,true)?'-all':'').'.json'),json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($errors?1:0);
