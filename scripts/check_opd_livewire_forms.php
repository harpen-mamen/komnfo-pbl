<?php

require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$settings=config('database.connections.mysql');$settings['database']='kominfo_recovery_preview_20261025';$settings['url']=null;config(['database.connections.recovery_preview'=>$settings]);Illuminate\Support\Facades\DB::setDefaultConnection('recovery_preview');
$db=Illuminate\Support\Facades\DB::connection();$db->beginTransaction();$errors=[];$checked=0;
try {
 $base=App\Models\User::where('email','kominfo@sipdt.test')->firstOrFail();
 foreach(App\Models\Dataset::with('fields','values')->whereHas('values')->where('is_active',true)->get() as $dataset){
  if(in_array('--edit-smoke',$argv,true) && !in_array($dataset->kode,['3.13','3.16','3.35','4.47','5.59'],true))continue;
  $actor=clone $base;$actor->opd_id=$dataset->opd_id;$actor->setRelation('roles',new Illuminate\Database\Eloquent\Collection([new Spatie\Permission\Models\Role(['name'=>'admin_opd','guard_name'=>'web'])]));Illuminate\Support\Facades\Auth::guard('web')->setUser($actor);
  $original=$dataset->values->first();$values=$original->values;
  if($dataset->kode==='5.59')$values=App\Services\DataInput\RoadInputDefinition::normalize($values);
  $data=['tahun'=>$original->tahun,'periode_label'=>$original->periode_label,'wilayah_type'=>$original->wilayah_type,'wilayah_id'=>$original->wilayah_id,'values'=>$values];
  try {
   $before=App\Models\DatasetValue::max('id');
   Livewire\Livewire::withQueryParams(['dataset'=>$dataset->kode])->test(App\Filament\Resources\DynamicDatasetResource\Pages\CreateDynamicDatasetValue::class)->fillForm($data)->call('create')->assertHasNoFormErrors();
   $created=App\Models\DatasetValue::where('id','>',$before)->where('dataset_id',$dataset->id)->first();
   if(!$created)throw new RuntimeException('No draft created');
   if(in_array('--edit-smoke',$argv,true))Livewire\Livewire::withQueryParams([])->test(App\Filament\Resources\DynamicDatasetResource\Pages\EditDynamicDatasetValue::class,['record'=>$created->id])->fillForm($data)->call('save')->assertHasNoFormErrors();
   foreach($original->values as $key=>$value) if($value!==null && ($created->values[$key]??null)!=$value)throw new RuntimeException('Original figure changed: '.$key);
   $checked++;
  }catch(Throwable $e){$errors[]=['code'=>$dataset->kode,'error'=>$e->getMessage()];echo json_encode(end($errors),JSON_UNESCAPED_UNICODE).PHP_EOL;}
 }
 echo json_encode(['forms_saved'=>$checked,'errors'=>$errors,'all_test_rows'=>'rolled back'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
}finally{$db->rollBack();}
exit($errors?1:0);
