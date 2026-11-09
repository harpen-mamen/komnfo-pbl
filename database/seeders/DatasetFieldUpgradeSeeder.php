<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DatasetFieldUpgradeSeeder
 *
 * Meng-upgrade field definitions untuk 7 dataset kritis yang
 * diperlukan untuk Buku Sektoral 2024:
 * - 3.20 e-PPGBM Stunting
 * - 3.21 AKI/AKB/AHH
 * - 4.47 Produksi per Ikan
 * - 4.48 Armada Kapal per GT
 * - 5.59 Jalan & Jembatan
 * - 6.72 Pedagang & Sarana
 * - 7.75 Realisasi Anggaran
 *
 * Upgrade ini:
 * - Menghapus field lama yang generic
 * - Menambahkan field baru sesuai struktur Buku Sektoral 2024
 * - Mempertahankan urutan field
 * - Mempertahankan tipe field (string/integer/decimal/enum/reference)
 * - Mempertahankan enum options
 */
class DatasetFieldUpgradeSeeder extends Seeder
{
    public function run(): void
    {
        $upgrades = require __DIR__ . '/data/SangiheOpdFieldUpgrade.php';

        foreach ($upgrades as $kode => $config) {
            $dataset = Dataset::where('kode', $kode)->whereNull('deleted_at')->first();
            if (! $dataset) {
                $this->command->warn("Dataset $kode tidak ditemukan, skip");
                continue;
            }

            $this->command->info("Upgrading {$dataset->nama} ({$kode})...");

            DB::transaction(function () use ($dataset, $config) {
                // Update deskripsi
                if (isset($config['deskripsi'])) {
                    $dataset->deskripsi = $config['deskripsi'];
                    $dataset->save();
                }

                // Hapus field lama
                $dataset->fields()->delete();

                // Buat field baru
                $urutan = 0;
                foreach ($config['fields'] as $fieldName => $fieldConfig) {
                    DatasetField::create([
                        'dataset_id' => $dataset->id,
                        'nama' => $fieldName,
                        'label' => $fieldConfig['label'] ?? Str::headline($fieldName),
                        'tipe' => $fieldConfig['tipe'],
                        'reference_target' => $fieldConfig['reference_target'] ?? null,
                        'required' => $fieldConfig['required'] ?? false,
                        'is_dimension' => $fieldConfig['is_dimension'] ?? false,
                        'options' => isset($fieldConfig['options']) ? json_encode($fieldConfig['options']) : null,
                        'satuan' => $fieldConfig['satuan'] ?? null,
                        'klasifikasi' => $fieldConfig['klasifikasi'] ?? null,
                        'urutan' => $urutan++,
                    ]);
                }
            });

            $newCount = $dataset->fields()->count();
            $this->command->info("  -> Updated with $newCount fields");
        }
    }
}
