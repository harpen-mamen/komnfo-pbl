<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetValue;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Import the published 2024 food adequacy tables (Buku Statistik Sektoral, tables 4.37–4.38). */
class StatistikSektoral2024PanganSeeder extends Seeder
{
    private const MARKER = '[STATISTIK-SEKTORAL-2024]';

    public function run(): void
    {
        $userId = User::query()->value('id');
        if (! $userId) {
            $this->command?->error('Tidak ada pengguna untuk mencatat sumber data laporan.');
            return;
        }

        $tables = [
            '4.37' => [
                ['jenis_bahan_makanan' => 'Padi-padian', 'kkal_per_hari' => 1080, 'persen_ake' => 51.4, 'gram_protein_per_hari' => 24.2, 'persen_akp' => 42.5],
                ['jenis_bahan_makanan' => 'Umbi-umbian', 'kkal_per_hari' => 118, 'persen_ake' => 5.6, 'gram_protein_per_hari' => 0.5, 'persen_akp' => 1.0],
                ['jenis_bahan_makanan' => 'Pangan Hewani', 'kkal_per_hari' => 168, 'persen_ake' => 8.0, 'gram_protein_per_hari' => 21.9, 'persen_akp' => 38.4],
                ['jenis_bahan_makanan' => 'Minyak/Lemak', 'kkal_per_hari' => 230, 'persen_ake' => 11.0, 'gram_protein_per_hari' => 0.0, 'persen_akp' => 0.1],
                ['jenis_bahan_makanan' => 'Buah/Biji', 'kkal_per_hari' => 52, 'persen_ake' => 2.5, 'gram_protein_per_hari' => 0.6, 'persen_akp' => 1.0],
                ['jenis_bahan_makanan' => 'Kacang-kacangan', 'kkal_per_hari' => 26, 'persen_ake' => 1.3, 'gram_protein_per_hari' => 2.6, 'persen_akp' => 4.5],
                ['jenis_bahan_makanan' => 'Gula', 'kkal_per_hari' => 108, 'persen_ake' => 5.2, 'gram_protein_per_hari' => 0.0, 'persen_akp' => 0.0],
                ['jenis_bahan_makanan' => 'Sayur/Buah', 'kkal_per_hari' => 90, 'persen_ake' => 4.3, 'gram_protein_per_hari' => 3.4, 'persen_akp' => 6.0],
                ['jenis_bahan_makanan' => 'Lain-lain', 'kkal_per_hari' => 33, 'persen_ake' => 1.6, 'gram_protein_per_hari' => 1.5, 'persen_akp' => 2.6],
                ['jenis_bahan_makanan' => 'Total', 'kkal_per_hari' => 1908, 'persen_ake' => 90.0, 'gram_protein_per_hari' => 54.7, 'persen_akp' => 96.0],
            ],
            '4.38' => [
                ['kelompok_pangan' => 'Padi-padian', 'berat_pangan' => 27.6, 'kkal_per_hari' => 1080, 'persen_energi' => 56.6, 'skor_aktual' => 28.3, 'skor_ake' => 25.7, 'skor_maksimal' => 25.0, 'skor_pph' => 25.0],
                ['kelompok_pangan' => 'Umbi-umbian', 'berat_pangan' => 59.1, 'kkal_per_hari' => 118, 'persen_energi' => 6.2, 'skor_aktual' => 3.1, 'skor_ake' => 2.8, 'skor_maksimal' => 2.5, 'skor_pph' => 2.5],
                ['kelompok_pangan' => 'Pangan Hewani', 'berat_pangan' => 135.9, 'kkal_per_hari' => 168, 'persen_energi' => 8.8, 'skor_aktual' => 17.7, 'skor_ake' => 16.0, 'skor_maksimal' => 24.0, 'skor_pph' => 16.0],
                ['kelompok_pangan' => 'Minyak/Lemak', 'berat_pangan' => 25.7, 'kkal_per_hari' => 230, 'persen_energi' => 12.1, 'skor_aktual' => 6.0, 'skor_ake' => 5.5, 'skor_maksimal' => 5.0, 'skor_pph' => 5.0],
                ['kelompok_pangan' => 'Buah/Biji', 'berat_pangan' => 9.7, 'kkal_per_hari' => 52, 'persen_energi' => 2.7, 'skor_aktual' => 1.4, 'skor_ake' => 1.2, 'skor_maksimal' => 1.0, 'skor_pph' => 1.0],
                ['kelompok_pangan' => 'Kacang-kacangan', 'berat_pangan' => 13.8, 'kkal_per_hari' => 26, 'persen_energi' => 1.4, 'skor_aktual' => 2.8, 'skor_ake' => 2.5, 'skor_maksimal' => 10.0, 'skor_pph' => 2.5],
                ['kelompok_pangan' => 'Gula', 'berat_pangan' => 29.6, 'kkal_per_hari' => 108, 'persen_energi' => 5.7, 'skor_aktual' => 2.8, 'skor_ake' => 2.6, 'skor_maksimal' => 2.5, 'skor_pph' => 2.5],
                ['kelompok_pangan' => 'Sayur/Buah', 'berat_pangan' => 190.7, 'kkal_per_hari' => 90, 'persen_energi' => 4.7, 'skor_aktual' => 23.6, 'skor_ake' => 21.5, 'skor_maksimal' => 30.0, 'skor_pph' => 21.5],
                ['kelompok_pangan' => 'Lain-lain', 'berat_pangan' => 46.4, 'kkal_per_hari' => 33, 'persen_energi' => 1.8, 'skor_aktual' => null, 'skor_ake' => null, 'skor_maksimal' => null, 'skor_pph' => null],
                ['kelompok_pangan' => 'Total', 'berat_pangan' => null, 'kkal_per_hari' => 1908, 'persen_energi' => 100.0, 'skor_aktual' => 85.7, 'skor_ake' => 77.9, 'skor_maksimal' => 100.0, 'skor_pph' => 76.0],
            ],
        ];

        DB::transaction(function () use ($tables, $userId): void {
            foreach ($tables as $code => $rows) {
                $dataset = Dataset::query()->where('kode', $code)
                    ->whereHas('opd', fn ($query) => $query->where('kode', 'KETPANGAN'))
                    ->where('is_active', true)->first();

                if (! $dataset) {
                    $this->command?->warn("Dataset {$code} OPD Ketahanan Pangan tidak ditemukan; tabel dilewati.");
                    continue;
                }

                if ($code === '4.37') {
                    $dimensionField = DatasetField::query()->where('dataset_id', $dataset->id)
                        ->where('nama', 'jenis_bahan_makanan')->first();
                    if ($dimensionField) {
                        $dimensionField->options = [
                            'Padi-padian' => 'Padi-padian', 'Umbi-umbian' => 'Umbi-umbian',
                            'Pangan Hewani' => 'Pangan Hewani', 'Minyak/Lemak' => 'Minyak dan Lemak',
                            'Buah/Biji' => 'Buah/Biji Berminyak', 'Kacang-kacangan' => 'Kacang-kacangan',
                            'Gula' => 'Gula', 'Sayur/Buah' => 'Sayur dan Buah',
                            'Lain-lain' => 'Lain-lain', 'Total' => 'Total',
                        ];
                        $dimensionField->save();
                    }
                }

                $expectedFields = array_keys($rows[0]);
                $actualFields = $dataset->fields()->pluck('nama')->all();
                if ($code === '4.38' && array_diff($expectedFields, $actualFields) !== []) {
                    $this->upgradePphFields($dataset);
                    $actualFields = $dataset->fields()->pluck('nama')->all();
                }
                if (array_diff($expectedFields, $actualFields) !== []) {
                    $this->command?->warn("Field dataset {$code} belum sesuai; jalankan DatasetFieldUpgradeSeeder sebelum seeder ini.");
                    continue;
                }

                DatasetValue::withTrashed()->where('dataset_id', $dataset->id)
                    ->where('notes', 'like', self::MARKER . '%')->forceDelete();

                foreach ($rows as $values) {
                    DatasetValue::create([
                        'dataset_id' => $dataset->id,
                        'tahun' => 2024,
                        'periode_label' => '2024',
                        'wilayah_type' => 'kabupaten',
                        'values' => $values,
                        'status' => DatasetValue::STATUS_PUBLISHED,
                        'status_verifikasi' => DatasetValue::STATUS_PUBLISHED,
                        'verified_at' => now(),
                        'verified_by' => $userId,
                        'published_at' => now(),
                        'created_by' => $userId,
                        'notes' => self::MARKER . " Bersumber dari Statistik Sektoral Kabupaten Kepulauan Sangihe Tahun 2024, Tabel {$code}; sumber tabel: Dinas Ketahanan Pangan Daerah.",
                    ]);
                }
                $this->command?->info("Tabel {$code}: " . count($rows) . ' baris laporan 2024 dimuat.');
            }
        });
    }

    /** Add the missing PPH metrics without changing other datasets or removing legacy fields. */
    private function upgradePphFields(Dataset $dataset): void
    {
        $fields = [
            'kelompok_pangan' => ['Kelompok Pangan', 'enum', true, null],
            'berat_pangan' => ['Berat Pangan', 'decimal', false, 'Gram/Kapita/Hari'],
            'kkal_per_hari' => ['Energi', 'decimal', false, 'Kkal/Kapita/Hari'],
            'persen_energi' => ['% Energi', 'decimal', false, 'Persen'],
            'skor_aktual' => ['Skor Aktual', 'decimal', false, 'Skor'],
            'skor_ake' => ['Skor AKE', 'decimal', false, 'Skor'],
            'skor_maksimal' => ['Skor Maksimal', 'decimal', false, 'Skor'],
            'skor_pph' => ['Skor PPH', 'decimal', false, 'Skor'],
        ];
        $options = [
            'Padi-padian' => 'Padi-padian', 'Umbi-umbian' => 'Umbi-umbian',
            'Pangan Hewani' => 'Pangan Hewani', 'Minyak/Lemak' => 'Minyak dan Lemak',
            'Buah/Biji' => 'Buah/Biji Berminyak', 'Kacang-kacangan' => 'Kacang-kacangan',
            'Gula' => 'Gula', 'Sayur/Buah' => 'Sayur dan Buah', 'Lain-lain' => 'Lain-lain',
            'Total' => 'Total',
        ];

        foreach ($fields as $index => [$label, $type, $isDimension, $unit]) {
            DatasetField::query()->updateOrCreate(
                ['dataset_id' => $dataset->id, 'nama' => $index],
                [
                    'label' => $label,
                    'tipe' => $type,
                    'required' => $index === 'kelompok_pangan',
                    'is_dimension' => $isDimension,
                    'options' => $isDimension ? $options : null,
                    'satuan' => $unit,
                    'urutan' => array_search($index, array_keys($fields), true),
                ]
            );
        }
    }
}
