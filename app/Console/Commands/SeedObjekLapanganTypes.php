<?php

namespace App\Console\Commands;

use App\Models\ObjekLapanganType;
use Illuminate\Console\Command;

/**
 * Seed default ObjekLapanganType (Prinsip 7).
 *
 * Tipe objek yang umum dikumpulkan Admin Desa:
 * - RUMAH (POINT) - WAJIB ada, rumah penduduk
 * - SEKOLAH (POINT) - sekolah/PAUD
 * - KANTOR (POINT) - kantor pemerintah, kantor desa
 * - PUSKESMAS (POINT) - puskesmas, pustu, posyandu
 * - TEMPAT_IBADAH (POINT) - masjid, gereja
 * - JALAN (LINESTRING) - ruas jalan
 * - JEMBATAN (POINT) - jembatan
 * - DRAINASE (LINESTRING) - drainase/got
 * - PASAR (POLYGON) - area pasar
 * - TERMINAL (POINT) - terminal, halte
 *
 * Usage:
 *   php artisan sipandu:seed-objek-lapangan-types
 *   php artisan sipandu:seed-objek-lapangan-types --dry-run
 */
class SeedObjekLapanganTypes extends Command
{
    protected $signature = 'sipandu:seed-objek-lapangan-types
                            {--dry-run : Preview without writing}';

    protected $description = 'Seed default ObjekLapanganType records (Prinsip 7)';

    protected array $types = [
        [
            'kode' => 'RUMAH',
            'nama' => 'Rumah',
            'geometry_type' => 'point',
            'icon' => 'heroicon-o-home',
            'color' => '#3B82F6',
            'field_schema' => [
                'kode_rumah' => ['tipe' => 'string', 'required' => false],
                'status_rumah' => ['tipe' => 'string', 'required' => false, 'options' => ['Milik Sendiri', 'Kontrak', 'Sewa', 'Dinas', 'Lainnya']],
                'keterangan' => ['tipe' => 'text', 'required' => false],
                'publik' => ['tipe' => 'boolean', 'required' => false],
            ],
            'urutan' => 1,
        ],
        [
            'kode' => 'SEKOLAH',
            'nama' => 'Sekolah',
            'geometry_type' => 'point',
            'icon' => 'heroicon-o-academic-cap',
            'color' => '#10B981',
            'field_schema' => [
                'nama_sekolah' => ['tipe' => 'string', 'required' => true],
                'jenjang' => ['tipe' => 'string', 'required' => true, 'options' => ['PAUD', 'TK', 'SD', 'SMP', 'SMA', 'SMK', 'SLB']],
                'status' => ['tipe' => 'string', 'required' => true, 'options' => ['Negeri', 'Swasta']],
                'kondisi' => ['tipe' => 'string', 'required' => false, 'options' => ['Baik', 'Rusak Ringan', 'Rusak Berat']],
                'jumlah_guru' => ['tipe' => 'integer', 'required' => false],
                'jumlah_murid' => ['tipe' => 'integer', 'required' => false],
            ],
            'urutan' => 2,
        ],
        [
            'kode' => 'KANTOR',
            'nama' => 'Kantor Pemerintahan',
            'geometry_type' => 'point',
            'icon' => 'heroicon-o-building-office',
            'color' => '#6366F1',
            'field_schema' => [
                'nama_kantor' => ['tipe' => 'string', 'required' => true],
                'jenis_kantor' => ['tipe' => 'string', 'required' => true, 'options' => ['Kantor Desa', 'Kantor Kecamatan', 'Kantor OPD', 'BUMD', 'Lainnya']],
                'kondisi_bangunan' => ['tipe' => 'string', 'required' => false, 'options' => ['Baik', 'Rusak Ringan', 'Rusak Berat']],
                'kepala_kantor' => ['tipe' => 'string', 'required' => false],
            ],
            'urutan' => 3,
        ],
        [
            'kode' => 'PUSKESMAS',
            'nama' => 'Fasilitas Kesehatan',
            'geometry_type' => 'point',
            'icon' => 'heroicon-o-heart',
            'color' => '#EF4444',
            'field_schema' => [
                'nama_faskes' => ['tipe' => 'string', 'required' => true],
                'jenis' => ['tipe' => 'string', 'required' => true, 'options' => ['Puskesmas', 'Pustu', 'Posyandu', 'Klinik', 'Lainnya']],
                'status_operasional' => ['tipe' => 'boolean', 'required' => true],
                'kepala' => ['tipe' => 'string', 'required' => false],
            ],
            'urutan' => 4,
        ],
        [
            'kode' => 'TEMPAT_IBADAH',
            'nama' => 'Tempat Ibadah',
            'geometry_type' => 'point',
            'icon' => 'heroicon-o-building-library',
            'color' => '#8B5CF6',
            'field_schema' => [
                'nama' => ['tipe' => 'string', 'required' => true],
                'jenis' => ['tipe' => 'string', 'required' => true, 'options' => ['Masjid', 'Mushola', 'Gereja', 'Pura', 'Vihara', 'Klenteng']],
                'kondisi' => ['tipe' => 'string', 'required' => false, 'options' => ['Baik', 'Rusak Ringan', 'Rusak Berat']],
            ],
            'urutan' => 5,
        ],
        [
            'kode' => 'JALAN',
            'nama' => 'Jalan',
            'geometry_type' => 'linestring',
            'icon' => 'heroicon-o-map',
            'color' => '#F59E0B',
            'field_schema' => [
                'nama_ruas' => ['tipe' => 'string', 'required' => true],
                'panjang_m' => ['tipe' => 'decimal', 'required' => true],
                'lebar_m' => ['tipe' => 'decimal', 'required' => false],
                'jenis_permukaan' => ['tipe' => 'string', 'required' => true, 'options' => ['Aspal', 'Beton', 'Paving', 'Tanah', 'Kerikil']],
                'kondisi' => ['tipe' => 'string', 'required' => true, 'options' => ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat']],
            ],
            'urutan' => 6,
        ],
        [
            'kode' => 'JEMBATAN',
            'nama' => 'Jembatan',
            'geometry_type' => 'point',
            'icon' => 'heroicon-o-arrows-right-left',
            'color' => '#0EA5E9',
            'field_schema' => [
                'nama' => ['tipe' => 'string', 'required' => true],
                'panjang_m' => ['tipe' => 'decimal', 'required' => true],
                'lebar_m' => ['tipe' => 'decimal', 'required' => false],
                'kondisi' => ['tipe' => 'string', 'required' => true, 'options' => ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat']],
            ],
            'urutan' => 7,
        ],
        [
            'kode' => 'DRAINASE',
            'nama' => 'Drainase / Saluran',
            'geometry_type' => 'linestring',
            'icon' => 'heroicon-o-bars-3',
            'color' => '#06B6D4',
            'field_schema' => [
                'panjang_m' => ['tipe' => 'decimal', 'required' => true],
                'lebar_m' => ['tipe' => 'decimal', 'required' => false],
                'kedalaman_m' => ['tipe' => 'decimal', 'required' => false],
                'kondisi' => ['tipe' => 'string', 'required' => true, 'options' => ['Baik', 'Sedang', 'Tersumbat', 'Rusak']],
            ],
            'urutan' => 8,
        ],
        [
            'kode' => 'PASAR',
            'nama' => 'Pasar',
            'geometry_type' => 'polygon',
            'icon' => 'heroicon-o-shopping-bag',
            'color' => '#F97316',
            'field_schema' => [
                'nama_pasar' => ['tipe' => 'string', 'required' => true],
                'jumlah_pedagang' => ['tipe' => 'integer', 'required' => false],
                'kondisi' => ['tipe' => 'string', 'required' => false, 'options' => ['Aktif', 'Tidak Aktif', 'Renovasi']],
            ],
            'urutan' => 9,
        ],
        [
            'kode' => 'POSYANDU',
            'nama' => 'Posyandu',
            'geometry_type' => 'point',
            'icon' => 'heroicon-o-user-group',
            'color' => '#EC4899',
            'field_schema' => [
                'nama_posyandu' => ['tipe' => 'string', 'required' => true],
                'strata' => ['tipe' => 'string', 'required' => false, 'options' => [' Pratama', 'Madya', 'Purnama', 'Mandiri']],
                'jumlah_kader' => ['tipe' => 'integer', 'required' => false],
            ],
            'urutan' => 10,
        ],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('========================================');
        $this->info('Seed ObjekLapanganType (Default Types)');
        $this->info('========================================');

        if ($dryRun) {
            $this->warn('DRY RUN MODE.');
        }

        $created = 0;
        $updated = 0;

        foreach ($this->types as $typeData) {
            $existing = ObjekLapanganType::where('kode', $typeData['kode'])->first();

            if (! $existing) {
                $this->line("  + Create: {$typeData['kode']} ({$typeData['nama']})");
                $created++;
                if (! $dryRun) {
                    ObjekLapanganType::create([
                        'kode' => $typeData['kode'],
                        'nama' => $typeData['nama'],
                        'geometry_type' => $typeData['geometry_type'],
                        'icon' => $typeData['icon'],
                        'color' => $typeData['color'],
                        'is_active' => true,
                        'field_schema' => $typeData['field_schema'],
                        'validation_rules' => null,
                        'urutan' => $typeData['urutan'],
                    ]);
                }
            } else {
                $this->line("  ~ Exists: {$typeData['kode']}");
                $updated++;
            }
        }

        $this->newLine();
        $this->info('Created: ' . $created);
        $this->info('Exists:  ' . $updated);

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN.');
        }

        return self::SUCCESS;
    }
}
