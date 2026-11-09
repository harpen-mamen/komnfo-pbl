<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ETL Command: Migrasi data dari tabel lama ke sistem generik baru.
 *
 * Usage:
 *   php artisan data:migrate-old-to-new --dry-run     # Cek tanpa simpan
 *   php artisan data:migrate-old-to-new               # Eksekusi migrasi
 *   php artisan data:migrate-old-to-new --only=kesehatan,pendidikan
 */
class MigrateOldToNew extends Command
{
    protected $signature = 'data:migrate-old-to-new
                            {--dry-run : Preview tanpa simpan}
                            {--only= : Hanya migrasi kategori tertentu (kesehatan,pendidikan,dll)}
                            {--chunk=100 : Batch size}';

    protected $description = 'ETL: Migrasi data dari tabel lama ke sistem generik (dataset_values)';

    // Statistik migrasi
    protected array $stats = [
        'total_processed' => 0,
        'total_migrated' => 0,
        'total_skipped' => 0,
        'total_errors' => 0,
        'by_table' => [],
    ];

    public function handle(): int
    {
        $this->info('=====================================');
        $this->info('  ETL: Migrasi Tabel Lama → Baru');
        $this->info('=====================================');
        $this->newLine();

        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('MODE: DRY RUN (tidak ada data yang disimpan)');
            $this->newLine();
        }

        // Pemetaan: tabel_lama => [opd_kode, dataset_kode, field_map]
        $migrations = $this->getMigrations();

        $only = $this->option('only');
        if ($only) {
            $allowedCategories = explode(',', $only);
            $migrations = array_filter(
                $migrations,
                fn ($m) => in_array($m['category'], $allowedCategories)
            );
        }

        if (empty($migrations)) {
            $this->error('Tidak ada migrasi yang akan dijalankan.');
            return 1;
        }

        $this->info('Akan memigrasi '.count($migrations).' tabel:');
        foreach ($migrations as $m) {
            $this->line('  • '.$m['source_table'].' → dataset:'.$m['dataset_code']);
        }
        $this->newLine();

        if (! $isDryRun && ! $this->confirm('Lanjutkan migrasi?', true)) {
            $this->info('Dibatalkan.');
            return 0;
        }

        foreach ($migrations as $migration) {
            $this->migrateTable($migration, $isDryRun);
        }

        $this->printStats();
        return 0;
    }

    /**
     * Definisi semua migrasi yang akan dijalankan
     */
    protected function getMigrations(): array
    {
        return [
            // KESEHATAN
            [
                'category' => 'kesehatan',
                'source_table' => 'data_kesehatan_bulanans',
                'opd_code' => 'DINKES',
                'dataset_code' => 'YANKES_PUSKESMAS',
                'field_map' => [
                    'kecamatan_id' => 'kecamatan_id',
                    'puskesmas' => 'puskesmas_id',
                    'kunjungan' => 'kunjungan_total',
                    'rawat_jalan' => 'rawat_jalan',
                    'rawat_inap' => 'rawat_inap',
                    'rujukan' => 'rujukan',
                    'persalinan' => 'persalinan_normal',
                    'ibu_hamil' => 'kunjungan_ibu_hamil',
                    'bayi' => 'bayi_lahir',
                    'imunisasi' => 'imunisasi_dasar_lengkap',
                    'stunting' => 'kasus_stunting_baru',
                    'gizi_buruk' => 'kasus_gizi_buruk',
                    'dokter' => 'dokter_umum',
                    'perawat' => 'perawat',
                    'bidan' => 'bidan',
                    'bulan' => 'bulan',
                    'tahun' => 'tahun',
                ],
            ],
            [
                'category' => 'kesehatan',
                'source_table' => 'ibu_hamils',
                'opd_code' => 'DINKES',
                'dataset_code' => 'IBU_HAMIL',
                'field_map' => [
                    'nama' => 'nama',
                    'nik' => 'nik',
                    'tanggal_lahir' => 'tanggal_lahir',
                    'alamat' => 'alamat',
                    'nama_suami' => 'nama_suami',
                    'tanggal_hpl' => 'hpl',
                    'usia_kehamilan_minggu' => 'usia_kehamilan_minggu',
                    'status_kehamilan' => 'status_kehamilan',
                    'kategori_resiko' => 'kategori_resiko',
                    'mendapat_fe' => 'mendapat_fe',
                    'mendapat_imunisasi_tt' => 'mendapat_tt',
                    'mendapat_konseling' => 'mendapat_konseling',
                    'catatan' => 'catatan',
                ],
            ],
            [
                'category' => 'kesehatan',
                'source_table' => 'bayis',
                'opd_code' => 'DINKES',
                'dataset_code' => 'BAYI_BALITA',
                'field_map' => [
                    'nama' => 'nama',
                    'nik' => 'nik',
                    'tanggal_lahir' => 'tanggal_lahir',
                    'jenis_kelamin' => 'jenis_kelamin',
                    'alamat' => 'alamat',
                    'berat_badan_lahir' => 'berat_lahir_kg',
                    'panjang_badan_lahir' => 'panjang_lahir_cm',
                    'imunisasi_hb0' => 'imunisasi_hb0',
                    'imunisasi_bcg' => 'imunisasi_bcg',
                    'status_gizi' => 'status_gizi',
                    'asi_eksklusif' => 'asi_eksklusif',
                ],
            ],

            // PENDIDIKAN
            [
                'category' => 'pendidikan',
                'source_table' => 'data_pendidikan_bulanans',
                'opd_code' => 'DISDIK',
                'dataset_code' => 'REKAP_SATUAN_PENDIDIKAN',
                'field_map' => [
                    'kecamatan_id' => 'kecamatan_id',
                    'tahun_ajaran' => 'tahun_ajaran',
                    'semester' => 'semester',
                    'jenjang' => 'jenjang',
                    'status_sekolah' => 'status_sekolah',
                    'jumlah_sekolah' => 'jumlah_sekolah',
                    'rombel' => 'jumlah_rombel',
                    'jumlah_siswa' => 'jumlah_total_siswa',
                    'guru_asn' => 'guru_pns',
                    'guru_pppk' => 'guru_pppk',
                    'guru_honorer' => 'guru_honorer',
                    'internet' => 'memiliki_internet',
                ],
            ],
            [
                'category' => 'pendidikan',
                'source_table' => 'data_siswas',
                'opd_code' => 'DISDIK',
                'dataset_code' => 'REKAP_PESERTA_DIDIK',
                'field_map' => [
                    'sekolah_id' => 'sekolah_id',
                    'tahun_ajaran' => 'tahun_ajaran',
                    'semester' => 'semester',
                    'jumlah_siswa_total' => 'jumlah_total_siswa',
                    'laki_laki' => 'total_laki',
                    'perempuan' => 'total_perempuan',
                    'putus_sekolah' => 'putus_sekolah',
                ],
            ],

            // DINSOS
            [
                'category' => 'sosial',
                'source_table' => 'penerima_bantuans',
                'opd_code' => 'DINSOS',
                'dataset_code' => 'PENERIMA_BANSOS',
                'field_map' => [
                    'nama_penerima' => 'nama',
                    'nik_penerima' => 'nik_penerima',
                    'nama_program' => 'nama_program',
                    'tahun' => 'tahun',
                    'nominal_diterima' => 'nominal',
                    'status' => 'status',
                ],
            ],

            // DISHUB
            [
                'category' => 'perhubungan',
                'source_table' => 'kecelakaan_lalins', // kalau ada
                'opd_code' => 'DISHUB',
                'dataset_code' => 'KECELAKAAN_LALIN',
                'field_map' => [
                    'tanggal' => 'tanggal',
                    'lokasi' => 'lokasi_objek_id',
                    'jenis_kecelakaan' => 'jenis_kecelakaan',
                    'korban_meninggal' => 'korban_meninggal',
                    'kerugian_rp' => 'kerugian_rp',
                ],
            ],
        ];
    }

    /**
     * Migrasi satu tabel
     */
    protected function migrateTable(array $migration, bool $isDryRun): void
    {
        $table = $migration['source_table'];
        $opdCode = $migration['opd_code'];
        $datasetCode = $migration['dataset_code'];
        $fieldMap = $migration['field_map'];

        $this->info("Migrasi: $table → $opdCode/$datasetCode");

        // Cek apakah tabel sumber ada
        if (! DB::getSchemaBuilder()->hasTable($table)) {
            $this->warn("  ⚠ Tabel $table tidak ada, skip");
            $this->stats['total_skipped']++;
            $this->stats['by_table'][$table] = 'tabel_tidak_ada';
            return;
        }

        // Cari dataset
        $dataset = Dataset::where('kode', $datasetCode)
            ->whereHas('opd', fn ($q) => $q->where('kode', $opdCode))
            ->first();
        if (! $dataset) {
            $this->error("  ✗ Dataset $datasetCode untuk $opdCode tidak ditemukan");
            $this->stats['total_errors']++;
            return;
        }

        // Hitung record
        $totalRecords = DB::table($table)->count();
        if ($totalRecords == 0) {
            $this->line("  ⊘ Tabel $table kosong, skip");
            $this->stats['by_table'][$table] = 'kosong';
            return;
        }

        $this->line("  → Ditemukan $totalRecords record");

        $migrated = 0;
        $errors = 0;
        $chunk = (int) $this->option('chunk');

        DB::table($table)->orderBy('id')->chunk($chunk, function ($records) use ($migration, $dataset, $fieldMap, $isDryRun, &$migrated, &$errors) {
            foreach ($records as $record) {
                try {
                    $values = [];
                    foreach ($fieldMap as $sourceCol => $targetField) {
                        if (isset($record->$sourceCol)) {
                            $values[$targetField] = $record->$sourceCol;
                        }
                    }

                    // Tambahkan metadata
                    $values['_legacy_id'] = $record->id;
                    $values['_migrated_at'] = now()->toIso8601String();

                    if (! $isDryRun) {
                        DatasetValue::create([
                            'dataset_id' => $dataset->id,
                            'opd_id' => $dataset->opd_id,
                            'values' => $values,
                            'status_verifikasi' => 'diverifikasi', // auto-approve legacy data
                            'created_by' => 1, // system
                            'created_at' => $record->created_at ?? now(),
                            'updated_at' => now(),
                        ]);
                    }
                    $migrated++;
                } catch (Exception $e) {
                    $errors++;
                    if ($errors <= 3) {
                        $this->error("    ✗ Error ID {$record->id}: ".$e->getMessage());
                    }
                }
            }
        });

        $this->stats['total_processed'] += $totalRecords;
        $this->stats['total_migrated'] += $migrated;
        $this->stats['total_errors'] += $errors;
        $this->stats['by_table'][$table] = "$migrated migrated, $errors errors";

        if ($migrated > 0) {
            $this->info("  ✓ Berhasil migrasi $migrated record".($errors > 0 ? " ($errors error)" : ''));
        }
        $this->newLine();
    }

    /**
     * Print statistik akhir
     */
    protected function printStats(): void
    {
        $this->newLine();
        $this->info('=====================================');
        $this->info('  STATISTIK MIGRASI');
        $this->info('=====================================');
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total record diproses', $this->stats['total_processed']],
                ['Total berhasil dimigrasi', $this->stats['total_migrated']],
                ['Total di-skip', $this->stats['total_skipped']],
                ['Total error', $this->stats['total_errors']],
            ]
        );
        $this->newLine();
        $this->info('Per Tabel:');
        foreach ($this->stats['by_table'] as $table => $result) {
            $this->line("  • $table: $result");
        }
    }
}
