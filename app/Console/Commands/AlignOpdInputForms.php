<?php

namespace App\Console\Commands;

use RuntimeException;
use Throwable;
use App\Models\Dataset;
use App\Services\DataInput\RoadInputDefinition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AlignOpdInputForms extends Command
{
    protected $signature='app:align-opd-input-forms {--database=recovery_preview} {--apply}';
    protected $description='Align reviewed sectoral input metadata without changing reported values.';

    public function handle(): int
    {
        $connection=$this->option('database');
        if (!in_array($connection,['mysql','recovery_preview'],true)) throw new RuntimeException('Unsupported target');
        $settings=config('database.connections.mysql');$settings['database']='kominfo_recovery_preview_20261025';$settings['url']=null;
        config(['database.connections.recovery_preview'=>$settings]);DB::setDefaultConnection($connection);
        $db=DB::connection();
        if (!in_array($db->getDatabaseName(),['kominfo','kominfo_recovery_preview_20261025'],true)) throw new RuntimeException('Unexpected database');
        if ($this->option('apply')) {
            $path=storage_path('backups/form-alignment-'.$db->getDatabaseName().'-'.now()->format('Ymd-His').'.json');
            File::put($path,json_encode(['datasets'=>$db->table('datasets')->get(),'fields'=>$db->table('dataset_fields')->get(),'values'=>$db->table('dataset_values')->get()],JSON_THROW_ON_ERROR));
            $this->info('Snapshot: '.$path);
        }
        $db->beginTransaction();
        try {
            $hidden=[
                '3.16'=>['apm','aps','apk'], '3.20'=>['kode_puskesmas'],
                '8.82'=>['tahun','jabatan','jumlah_personil'], '8.85'=>['tahun','nilai_persen'],
                '8.86'=>['tahun','uraian','jumlah_unit'], '5.59'=>['kategori','uraian'],
            ];
            foreach ($hidden as $code=>$names) {
                $dataset=Dataset::where('kode',$code)->sole();
                $dataset->config=array_merge($dataset->config??[],['input_hidden_fields'=>$names]);$dataset->save();
                $dataset->fields()->whereIn('nama',$names)->update(['required'=>false]);
            }
            $changes=[
                '3.16'=>['jumlah_sekolah'=>['tipe'=>'integer','satuan'=>'sekolah','required'=>true], 'penduduk_usia_sekolah'=>['tipe'=>'integer','satuan'=>'jiwa','required'=>true]],
                '3.35'=>['kecamatan'=>['tipe'=>'reference','reference_target'=>'wilayahs','is_dimension'=>true,'required'=>true]],
                '3.36'=>['kecamatan'=>['tipe'=>'reference','reference_target'=>'wilayahs','is_dimension'=>true,'required'=>true]],
                '4.47'=>['harga_rata_rp_kg'=>['required'=>false,'definisi'=>'Harga rata-rata per kg. Biarkan kosong jika sumber tidak menyediakan angka yang dapat dipastikan.']],
                '8.82'=>['golongan'=>['required'=>true,'label'=>'Status kepegawaian','is_dimension'=>true], 'jumlah'=>['required'=>true,'label'=>'Jumlah personel','tipe'=>'integer','satuan'=>'orang']],
                '8.85'=>['nilai'=>['required'=>true,'label'=>'Persentase indikator','satuan'=>'persen']],
                '8.86'=>['indikator'=>['required'=>true,'label'=>'Jenis sarana pengelolaan sampah','is_dimension'=>true], 'jumlah'=>['required'=>true,'label'=>'Jumlah sarana','tipe'=>'integer','satuan'=>'unit']],
            ];
            foreach ($changes as $code=>$fields) {
                $dataset=Dataset::where('kode',$code)->sole();
                foreach ($fields as $name=>$attributes) $dataset->fields()->where('nama',$name)->firstOrFail()->update($attributes);
            }
            foreach (['3.33','3.34'] as $code) {
                $dataset=Dataset::with('values')->where('kode',$code)->sole();
                $field=$dataset->fields()->where('nama','indikator')->firstOrFail();
                $options=$field->options??[];if(is_string($options))$options=json_decode($options,true,512,JSON_THROW_ON_ERROR);
                if(array_is_list($options))$options=array_combine($options,$options)?:[];
                foreach($dataset->values as $value) if(is_string($label=$value->values['indikator']??null))$options[$label]??=$label;
                $field->options=$options;$field->save();
            }
            foreach (['3.35','3.36'] as $code) Dataset::where('kode',$code)->update(['period_type'=>'tahunan']);
            $road=Dataset::where('kode','5.59')->sole();
            $options=[];foreach(RoadInputDefinition::indicators() as $key=>$definition)$options[$key]=$definition[1];
            $road->fields()->updateOrCreate(['nama'=>'indikator_jalan'],['label'=>'Indikator jalan / jembatan','tipe'=>'enum','required'=>true,'is_dimension'=>true,'options'=>$options,'urutan'=>0]);
            $road->fields()->updateOrCreate(['nama'=>'satuan'],['label'=>'Satuan otomatis','tipe'=>'string','required'=>false,'urutan'=>1]);
            $road->fields()->where('nama','nilai')->update(['satuan'=>null,'label'=>'Angka sesuai indikator']);
            $this->info('Metadata aligned; no dataset_values changed.');
            if($this->option('apply'))$db->commit();else{$db->rollBack();$this->info('Dry run rolled back.');}
        } catch(Throwable $e){$db->rollBack();throw $e;}
        return self::SUCCESS;
    }
}
