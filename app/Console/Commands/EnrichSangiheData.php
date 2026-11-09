<?php

namespace App\Console\Commands;

use RuntimeException;
use Throwable;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EnrichSangiheData extends Command
{
    protected $signature = 'app:enrich-sangihe-data {--database=recovery_preview} {--apply : Commit the reviewed import; otherwise roll back}';
    protected $description = 'Import traced sectoral statistics and public infrastructure reference points, preserving other records.';
    private int $added = 0;
    private int $existing = 0;

    public function handle(): int
    {
        $connection = (string) $this->option('database');
        if (! in_array($connection, ['mysql', 'recovery_preview'], true)) throw new RuntimeException('Unsupported import connection.');
        $settings = config('database.connections.mysql');
        $settings['database'] = 'kominfo_recovery_preview_20261025';
        $settings['url'] = null;
        config(['database.connections.recovery_preview' => $settings]);
        $original = DB::getDefaultConnection();
        DB::setDefaultConnection($connection);
        $db = DB::connection();
        if (! in_array($db->getDatabaseName(), ['kominfo', 'kominfo_recovery_preview_20261025'], true)) throw new RuntimeException('Unexpected target database.');

        if ($this->option('apply')) {
            $backup = storage_path('backups/enrichment-'.$db->getDatabaseName().'-'.now()->format('Ymd-His').'.json');
            File::ensureDirectoryExists(dirname($backup), 0700);
            File::put($backup, json_encode([
                'created_at'=>now()->toIso8601String(), 'database'=>$db->getDatabaseName(),
                'datasets'=>$db->table('datasets')->get(), 'dataset_fields'=>$db->table('dataset_fields')->get(), 'dataset_values'=>$db->table('dataset_values')->get(),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            $this->info('Snapshot: '.$backup);
        }
        $db->beginTransaction();
        try {
            $tables = require database_path('seeders/data/SangiheSectoral2024Supplement.php');
            foreach ($tables as $code => $table) {
                $dataset = Dataset::where('kode', (string) $code)->firstOrFail();
                if (isset($table['title'])) $dataset->nama = $table['title'];
                $this->prepareFields($dataset, $table);
                $config = $dataset->config ?? [];
                $dataset->config = array_merge($config, [
                    'chart_dimensions'=>$table['key'],
                    'chart_unit_field'=>collect($table['rows'])->contains(fn ($row) => isset($row['satuan'])) ? 'satuan' : null,
                    'source_publication'=>'Statistik Sektoral Kabupaten Kepulauan Sangihe Tahun Data 2024',
                    'source_url'=>'https://sangihekab.go.id/statistik-sektoral-tahun-data-2024/',
                    'source_page'=>$table['page'], 'source_table'=>(string) $code,
                ]);
                $dataset->save();
                foreach ($table['rows'] as $row) {
                    $key = implode('|', array_map(fn ($name) => (string) ($row[$name] ?? ''), $table['key']));
                    $source = "Statistik Sektoral Kabupaten Kepulauan Sangihe Tahun Data 2024; tabel {$code}, halaman {$table['page']}. https://sangihekab.go.id/statistik-sektoral-tahun-data-2024/ ".($table['note'] ?? 'Angka mengikuti tabel sumber. Tidak tersedia rincian lokasi/koordinat.');
                    $this->insertSourcedValue($dataset, $row, '2024-'.$code.'-'.$key, 2024, $source);
                }
                $this->line('Table '.$code.': '.count($table['rows']).' source rows');
            }
            $points = require database_path('seeders/data/SangiheInfrastructureSources.php');
            foreach ($points as $point) {
                $dataset = $this->spatialDataset($point['code'], $point['opd']);
                $lat = $point['latitude']; $lon = $point['longitude'];
                if ($lat < 3 || $lat > 5.7 || $lon < 125 || $lon > 126.3) throw new RuntimeException('Coordinate outside Sangihe envelope.');
                $values = [
                    'nama'=>$point['name'], 'jenis'=>$point['kind'], 'latitude'=>$lat, 'longitude'=>$lon,
                    'sumber_url'=>$point['source_url'], 'tanggal_akses'=>'2026-10-25',
                    'status_lokasi'=>'Rujukan sumber publik; bukan survei lapangan',
                    'geometri'=>['type'=>'Feature', 'geometry'=>['type'=>'Point','coordinates'=>[$lon,$lat]], 'properties'=>[
                        'source_url'=>$point['source_url'], 'source_reference'=>$point['reference'],
                        'retrieved_at'=>'2026-10-25', 'verification'=>'source-reported',
                    ]],
                ];
                $this->insertSourcedValue($dataset, $values, 'geo-'.$point['code'].'-'.$point['name'], 2026, $point['source_url'].' '.$point['reference'].' Tahun 2026 adalah tahun akses, bukan tahun pembangunan atau survei.');
            }
            // Register facilities with rejected coordinates without placing them
            // on the map or replacing a wrong latitude by its absolute value.
            $reviewFile = storage_path('app/source-imports/2026-10-25/facilities-review.json');
            if (File::exists($reviewFile)) {
                $review = json_decode(ltrim(File::get($reviewFile), "\xEF\xBB\xBF"), true, 512, JSON_THROW_ON_ERROR);
                foreach ($review as $facility) {
                    if ($facility['accepted']) continue;
                    $dataset = $this->spatialDataset('LOKASI_FASKES_PUBLIK', 'DINKES');
                    $this->insertSourcedValue($dataset, [
                        'nama'=>$facility['name'], 'jenis'=>'Puskesmas', 'sumber_url'=>$facility['source_url'],
                        'tanggal_akses'=>'2026-10-25', 'status_lokasi'=>'Koordinat sumber ditolak: di luar wilayah Sangihe',
                    ], 'geo-LOKASI_FASKES_PUBLIK-'.$facility['name'], 2026,
                        $facility['source_url'].' Koordinat sumber berada di luar Sangihe; geometri tidak diimpor. Jangan gunakan titik fallback atau membalik tanda lintang.');
                }
            }
            $this->info('New: '.$this->added.'; already imported: '.$this->existing.'; map points: '.count($points));
            if ($this->option('apply')) {
                $db->commit();
                $this->info('Committed to '.$db->getDatabaseName());
            } else {
                $db->rollBack();
                $this->info('Dry run rolled back.');
            }
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        } finally {
            DB::setDefaultConnection($original);
        }
        return self::SUCCESS;
    }

    private function prepareFields(Dataset $dataset, array $table): void
    {
        foreach (collect($table['rows'])->flatMap(fn ($row) => array_keys($row))->unique() as $name) {
            $field = $dataset->fields()->firstOrNew(['nama'=>$name]);
            $samples = collect($table['rows'])->pluck($name)->filter(fn ($v) => $v !== null);
            if (! $field->exists) {
                $field->fill(['label'=>ucwords(str_replace('_',' ',$name)), 'tipe'=>$samples->every(fn ($v)=>is_int($v)||is_float($v))?'decimal':'string', 'required'=>false,
                    'urutan'=>(int)$dataset->fields()->max('urutan')+1]);
            }
            if ($field->tipe === 'enum') {
                $options = $field->options ?? [];
                if (is_string($options)) $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);
                if (array_is_list($options)) $options = array_combine($options, $options) ?: [];
                foreach ($samples as $sample) $options[(string)$sample] ??= (string)$sample;
                $field->options = $options;
            }
            if (in_array($name, $table['key'], true)) {
                $field->is_dimension = true;
                $field->urutan = array_search($name, $table['key'], true);
            }
            if ($name === 'bulan') $field->required = false;
            $field->save();
        }
        // Annual source tables do not provide an invented month.
        $dataset->fields()->where('nama','bulan')->update(['required'=>false]);
    }

    private function spatialDataset(string $code, string $opd): Dataset
    {
        $dataset = Dataset::firstOrCreate(['opd_id'=>Opd::where('kode',$opd)->sole()->id,'kode'=>$code],[
            'nama'=>$opd === 'DINKES' ? 'Lokasi Fasilitas Kesehatan — Rujukan Publik' : 'Lokasi Infrastruktur Transportasi — Rujukan Publik',
            'deskripsi'=>'Titik lokasi dari sumber pemerintah terbuka. Tahun data adalah tahun akses; bukan inventaris aset hasil survei.',
            'period_type'=>'master','geometry_type'=>'point','is_active'=>true,'requires_verification'=>true,
            'lisensi'=>'open_data','sumber_data'=>'Sumber pemerintah publik','config'=>['chart_dimensions'=>['jenis']],
        ]);
        foreach (['nama'=>'string','jenis'=>'string','latitude'=>'decimal','longitude'=>'decimal','sumber_url'=>'string','tanggal_akses'=>'date','status_lokasi'=>'string','geometri'=>'json'] as $name=>$type) {
            $dataset->fields()->firstOrCreate(['nama'=>$name],[
                'label'=>ucwords(str_replace('_',' ',$name)), 'tipe'=>$type, 'required'=>false,
                'is_dimension'=>$name === 'jenis','lookup_source'=>$name === 'geometri' ? 'geometry:point' : null,
                'urutan'=>array_search($name,['nama','jenis','latitude','longitude','sumber_url','tanggal_akses','status_lokasi','geometri'],true),
            ]);
        }
        return $dataset;
    }

    private function insertSourcedValue(Dataset $dataset, array $values, string $key, int $year, string $source): void
    {
        $marker = '[SANGIHE-SOURCE:'.substr(hash('sha256',$key),0,24).']';
        if ($dataset->values()->withTrashed()->where('notes','like',$marker.'%')->exists()) {
            $this->existing++;
            return;
        }
        DatasetValue::create([
            'dataset_id'=>$dataset->id, 'tahun'=>$year, 'periode_label'=>(string)$year,
            'wilayah_type'=>'kabupaten', 'values'=>$values, 'status'=>'published','status_verifikasi'=>'published',
            'published_at'=>now(), 'created_by'=>DB::table('users')->where('email','kominfo@sipdt.test')->value('id'),
            'notes'=>$marker.' '.$source,
        ]);
        $this->added++;
    }
}
