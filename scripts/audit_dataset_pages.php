<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$settings = config('database.connections.mysql');
$settings['database']='kominfo_recovery_preview_20261025'; $settings['url']=null;
config(['database.connections.recovery_preview'=>$settings]);
Illuminate\Support\Facades\DB::setDefaultConnection('recovery_preview');
$options = getopt('', ['offset::','limit::']);
$offset=(int)($options['offset']??0); $limit=(int)($options['limit']??30);
$user = App\Models\User::where('email','kominfo@sipdt.test')->firstOrFail();
$datasets = App\Models\Dataset::orderBy('id')->offset($offset)->limit($limit)->get();
$errors=[]; $checked=0;
foreach ($datasets as $dataset) {
    $actor=clone $user;
    $actor->opd_id=$dataset->opd_id;
    $actor->setRelation('roles', new Illuminate\Database\Eloquent\Collection([new Spatie\Permission\Models\Role(['name'=>'admin_opd','guard_name'=>'web'])]));
    Illuminate\Support\Facades\Auth::guard('web')->setUser($actor);
    foreach ([
        '/admin/dataset-values?dataset='.urlencode($dataset->kode),
        '/admin/dataset-values/create?dataset='.urlencode($dataset->kode),
        '/admin/infografis-dataset/'.urlencode($dataset->kode),
    ] as $path) {
        $response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create($path,'GET'));
        $checked++;
        if ($response->getStatusCode()!==200) {
            preg_match('/<title>(.*?)<\/title>/s',$response->getContent(),$title);
            $errors[]=['code'=>$dataset->kode,'path'=>$path,'status'=>$response->getStatusCode(),'error'=>html_entity_decode(strip_tags($title[1]??'Unknown'))];
            echo json_encode(end($errors),JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    }
    if ($checked%30===0) echo 'Checked '.$checked.' responses'.PHP_EOL;
}
$result=['offset'=>$offset,'datasets'=>$datasets->count(),'responses'=>$checked,'errors'=>$errors];
Illuminate\Support\Facades\File::put(storage_path('app/source-imports/2026-10-25/dataset-audit-'.$offset.'.json'),json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo 'Offset '.$offset.': '.$checked.' responses; '.count($errors).' failures'.PHP_EOL;
exit($errors ? 1 : 0);
