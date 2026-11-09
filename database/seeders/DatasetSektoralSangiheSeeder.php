<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetCategory;
use App\Models\Opd;
use Illuminate\Database\Seeder;

/**
 * SEEDER: 93 Dataset Sektoral Sangihe (Compliance Perbup 30/2024)
 *
 * Sesuai Buku Data Statistik Sektoral Kabupaten Kepulauan Sangihe 2024
 * - Standar Data: 5 Elemen (Konsep, Definisi, Klasifikasi, Ukuran, Satuan)
 * - Pembina Data: BPS, DPUPR, Kominfo
 * - Setiap dataset di-link ke Kategori Urusan Pemerintah
 */
class DatasetSektoralSangiheSeeder extends Seeder
{
    public function run(): void
    {
        $totalDatasets = 0;
        $totalFields = 0;

        // Load semua blueprint
        $files = glob(__DIR__ . "/data/_93dataset_bab*.json");
        foreach ($files as $file) {
            $blueprint = json_decode(file_get_contents($file), true);
            if (!$blueprint) continue;

            foreach ($blueprint as $opdCode => $config) {
                list($categoryKode, $datasets) = $config;

                $canonicalCode = ['BKPSDMD' => 'BKPSDM', 'DPUPR' => 'PUPR', 'DISTAMAN' => 'DISTAN'][$opdCode] ?? $opdCode;
                $opd = Opd::where("kode", $canonicalCode)->first();
                if (!$opd) {
                    $this->command->warn("OPD $opdCode tidak ditemukan, skip");
                    continue;
                }

                $category = DatasetCategory::where("kode", $categoryKode)->first();

                foreach ($datasets as $kodeSdi => $ds) {
                    list($nama, $periodType, $fields) = $ds;

                    // Cari atau buat dataset
                    $dataset = Dataset::updateOrCreate(
                        ["kode" => $kodeSdi, "opd_id" => $opd->id],
                        [
                            "nama" => $nama,
                            "deskripsi" => "Dataset Sektoral Sangihe: " . $nama,
                            "kategori" => $category ? $category->nama : "Lainnya",
                            "category_id" => $category ? $category->id : null,
                            "kode_sdi" => $kodeSdi,
                            "period_type" => $periodType,
                            "cakupan_wilayah" => "kabupaten",
                            "sumber_data" => "Administrasi",
                            "klasifikasi_standar" => "BPS",
                            "lisensi" => "cc_by_4",
                            "format_data" => "JSON, CSV, XLSX",
                            "bahasa" => "id",
                            "is_active" => true,
                            "requires_verification" => true,
                            "status_aktif" => "aktif",
                            "pembina_data" => $opd->kode === "DISKOMINFO" ? "kominfo" : "bps",
                        ]
                    );

                    $totalDatasets++;

                    // Hapus field lama, recreate
                    $dataset->fields()->delete();

                    $urutan = 0;
                    foreach ($fields as $fieldName => $fieldConfig) {
                        list($label, $tipe, $isDimension, $extra) = $fieldConfig;
                        $lookupSource = $fieldConfig[4] ?? null;
                        $options = null;
                        $satuan = null;

                        if ($lookupSource !== null) {
                            // Lookup khusus, misalnya tipe geometri untuk field peta.
                        } elseif (is_array($extra)) {
                            $options = $extra;
                        } elseif (is_string($extra) && $extra === "wilayah") {
                            $lookupSource = "wilayah";
                        } elseif (is_string($extra)) {
                            $satuan = $extra;
                        }

                        DatasetField::create([
                            "dataset_id" => $dataset->id,
                            "nama" => $fieldName,
                            "label" => $label,
                            "tipe" => $tipe,
                            "required" => false,
                            "options" => $options,
                            "satuan" => $satuan,
                            "konsep" => "Konsep data " . $label,
                            "definisi" => "Definisi resmi data " . $label,
                            "klasifikasi" => $tipe === "enum" ? (is_array($extra) ? implode(", ", $extra) : null) : null,
                            "ukuran" => $tipe === "integer" || $tipe === "decimal" ? "Numerik" : "Teks",
                            "satuan" => $satuan,
                            "is_dimension" => $isDimension,
                            "is_primary_key" => false,
                            "lookup_source" => $lookupSource,
                            "urutan" => $urutan++,
                        ]);
                        $totalFields++;
                    }
                }
            }
        }

        $this->command->info("Berhasil seed $totalDatasets datasets dan $totalFields fields");
    }
}
