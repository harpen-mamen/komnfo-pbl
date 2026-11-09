<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\ObjekLapanganType;
use App\Models\Opd;
use Illuminate\Database\Seeder;

class OpdDatasetSangiheSeeder extends Seeder
{
    public function run(): void
    {
        $blueprint = require __DIR__.'/data/SangiheOpdDatasetsBlueprint.php';

        $totalDatasets = 0;
        $totalFields = 0;

        foreach ($blueprint as $opdCode => $opdConfig) {
            $canonicalCode = ['BKPSDMD' => 'BKPSDM', 'DPUPR' => 'PUPR', 'DISTAMAN' => 'DISTAN'][$opdCode] ?? $opdCode;
            $opd = Opd::where('kode', $canonicalCode)->first();
            if (! $opd) {
                $this->command->warn("OPD $opdCode tidak ditemukan, skip");
                continue;
            }

            foreach ($opdConfig['datasets'] as $datasetCode => $config) {
                $dataset = Dataset::updateOrCreate(
                    ['opd_id' => $opd->id, 'kode' => $datasetCode],
                    [
                        'nama' => $config['nama'],
                        'deskripsi' => $config['deskripsi'] ?? null,
                        'kategori' => $config['kategori'] ?? null,
                        'period_type' => $config['period_type'] ?? 'bulanan',
                        'geometry_type' => $config['geometry_type'] ?? 'none',
                        'cakupan_wilayah' => $config['cakupan_wilayah'] ?? 'kabupaten',
                        'sumber_data' => $config['sumber_data'] ?? null,
                        'klasifikasi_standar' => $config['klasifikasi_standar'] ?? null,
                        'lisensi' => $config['lisensi'] ?? 'cc_by_4',
                        'format_data' => 'JSON, CSV, XLSX',
                        'bahasa' => 'id',
                        'is_active' => true,
                        'requires_verification' => true,
                        'status_aktif' => 'aktif',
                    ]
                );

                $totalDatasets++;

                // Hapus field lama, recreate
                $dataset->fields()->delete();
                $urutan = 0;
                foreach ($config['fields'] ?? [] as $fieldName => $fieldConfig) {
                    DatasetField::create([
                        'dataset_id' => $dataset->id,
                        'nama' => $fieldName,
                        'label' => $fieldConfig['label'] ?? $fieldName,
                        'tipe' => $fieldConfig['tipe'] ?? 'string',
                        'reference_target' => $fieldConfig['reference_target'] ?? null,
                        'required' => $fieldConfig['required'] ?? false,
                        'options' => isset($fieldConfig['options']) ? $fieldConfig['options'] : null,
                        'validation_rules' => isset($fieldConfig['keterangan']) ? ['keterangan' => $fieldConfig['keterangan']] : null,
                        'lookup_source' => $fieldConfig['lookup_source'] ?? null,
                        'urutan' => $urutan++,
                    ]);
                    $totalFields++;
                }
            }
        }

        $this->command->info("Berhasil seeding $totalDatasets datasets dengan $totalFields fields untuk ".count($blueprint)." OPD");
    }
}
