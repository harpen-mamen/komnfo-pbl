<?php

require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$active=in_array('--active',$argv,true);
if(!$active){$settings=config('database.connections.mysql');$settings['database']='kominfo_recovery_preview_20261025';$settings['url']=null;config(['database.connections.recovery_preview'=>$settings]);Illuminate\Support\Facades\DB::setDefaultConnection('recovery_preview');}
$db=Illuminate\Support\Facades\DB::connection();$db->beginTransaction();$errors=[];$rows=0;$saved=0;$datasets=0;
try{
 $base=App\Models\User::where('email','kominfo@sipdt.test')->firstOrFail();
 foreach(App\Models\Dataset::with('fields','values')->where('is_active',true)->get() as $dataset){
  $datasets++;
  $actor=clone $base;$actor->opd_id=$dataset->opd_id;$actor->setRelation('roles',new Illuminate\Database\Eloquent\Collection([new Spatie\Permission\Models\Role(['name'=>'admin_opd','guard_name'=>'web'])]));Illuminate\Support\Facades\Auth::guard('web')->setUser($actor);
  foreach($dataset->values as $original){
   $rows++;$values=$original->values;if($dataset->kode==='5.59')$values=App\Services\DataInput\RoadInputDefinition::normalize($values);
   $data=['dataset_id'=>$dataset->id,'tahun'=>$original->tahun,'periode_label'=>$original->periode_label,'wilayah_type'=>$original->wilayah_type,'wilayah_id'=>$original->wilayah_id,'values'=>$values];
   $validator=validator($data,App\Services\DataInput\DatasetFieldResolver::buildValidationRules($dataset));
   if($validator->fails()){$errors[]=['code'=>$dataset->kode,'id'=>$original->id,'errors'=>$validator->errors()->toArray()];continue;}
   $calculated=App\Services\DataInput\FieldAutoCalculator::apply($dataset,$values);
   foreach($values as $key=>$value)if($value!==null && $value!=='' && ($calculated[$key]??null)!=$value)$errors[]=['code'=>$dataset->kode,'overwritten'=>$key];
   if(!$active){
    $manager=app(App\Services\DataInput\DatasetValueManager::class);$draft=$manager->createDraft($dataset,$data);$manager->updateDraft($draft,$data);$saved++;
    foreach($original->values as $key=>$value)if($value!==null && ($draft->fresh()->values[$key]??null)!=$value)$errors[]=['code'=>$dataset->kode,'saved_value_changed'=>$key];
   }
  }
 }
 $road=App\Models\Dataset::with('fields')->where('kode','5.59')->sole();
 $input=['dataset_id'=>$road->id,'tahun'=>2026,'values'=>App\Services\DataInput\RoadInputDefinition::normalize(['indikator_jalan'=>'jumlah_jembatan','nilai'=>5])];
 if(validator($input,App\Services\DataInput\DatasetFieldResolver::buildValidationRules($road))->fails())$errors[]='Structured bridge rejected';
 $charts=app(App\Services\DynamicChartService::class)->statisticsForDataset($road,collect([new App\Models\DatasetValue(['tahun'=>2026,'values'=>$input['values']])]));
 if(count($charts)!==1||$charts[0]['unit']!=='unit'||$charts[0]['values']!==[5.0])$errors[]='Structured bridge invisible in infographic';
 $input['values']['nilai']=1.5;if(validator($input,App\Services\DataInput\DatasetFieldResolver::buildValidationRules($road))->passes())$errors[]='Fractional bridge accepted';
 echo json_encode(['database'=>$db->getDatabaseName(),'datasets'=>$datasets,'rows_checked'=>$rows,'preview_create_update'=>$saved,'errors'=>$errors],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
}finally{$db->rollBack();}
exit($errors?1:0);
