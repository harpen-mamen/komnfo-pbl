<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\Dataset::with('fields','values')->where('opd_id',17)->get() as $dataset) {
    echo json_encode(['code'=>$dataset->kode,'name'=>$dataset->nama,'config'=>$dataset->config,'fields'=>$dataset->fields->map(fn($f)=>[$f->nama,$f->label,$f->satuan,$f->ukuran,$f->tipe])->all(),'values'=>$dataset->values->map(fn($v)=>['year'=>$v->tahun,'values'=>$v->values,'notes'=>$v->notes])->all()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
}
