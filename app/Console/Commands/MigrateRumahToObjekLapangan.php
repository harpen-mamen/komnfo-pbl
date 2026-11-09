<?php

namespace App\Console\Commands;

use Throwable;
use App\Models\ObjekLapangan;
use App\Models\ObjekLapanganType;
use App\Models\Opd;
use App\Models\Rumah;
use App\Models\SourceAttribution;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * FASE 4: Migrate Rumah -> ObjekLapangan
 *
 * Tabel `rumah` di codebase saat ini sebenarnya adalah
 * DATA LAPANGAN (Prinsip 5) - dikumpulkan Admin Desa via mobile.
 *
 * Tabel `objek_lapangans` adalah generic root untuk SEMUA objek
 * lapangan (Rumah, Sekolah, Kantor, Jalan, Jembatan, dll).
 *
 * Migration strategy: BACKWARD-COMPATIBLE
 * 1. Tetap pertahankan tabel `rumah` (jangan drop)
 * 2. Buat ObjekLapanganType dengan kode 'RUMAH'
 * 3. Create ObjekLapangan record untuk setiap Rumah
 * 4. Sinkron dua arah via Rumah::objekLapangan() relationship
 *
 * Tabel `rumah` di-deprecate secara gradual:
 *  - Masih bisa dibaca (existing API/UI)
 *  - Tidak ada CREATE/UPDATE langsung (semua lewat ObjekLapangan)
 *  - Drop setelah 1 bulan masa transisi
 *
 * Usage:
 *   php artisan sipandu:migrate-rumah-to-objek-lapangan
 *   php artisan sipandu:migrate-rumah-to-objek-lapangan --dry-run
 *   php artisan sipandu:migrate-rumah-to-objek-lapangan --batch-size=500
 */
class MigrateRumahToObjekLapangan extends Command
{
    protected $signature = 'sipandu:migrate-rumah-to-objek-lapangan
                            {--dry-run : Preview without writing}
                            {--batch-size=500 : Batch size for processing}';

    protected $description = 'Migrate existing Rumah records to ObjekLapangan (FASE 4)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');

        $this->info('========================================');
        $this->info('FASE 4: Migrate Rumah -> ObjekLapangan');
        $this->info('========================================');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - tidak ada data yang akan ditulis.');
        }

        $this->newLine();

        // 1. Pastikan ObjekLapanganType 'RUMAH' ada
        $rumahType = ObjekLapanganType::firstOrCreate(
            ['kode' => 'RUMAH'],
            [
                'nama' => 'Rumah',
                'geometry_type' => ObjekLapanganType::GEOMETRY_POINT,
                'icon' => 'heroicon-o-home',
                'color' => '#3B82F6',
                'is_active' => true,
                'field_schema' => json_encode([
                    'kode_rumah' => ['tipe' => 'string', 'required' => false],
                    'status_rumah' => ['tipe' => 'string', 'required' => false],
                    'keterangan' => ['tipe' => 'text', 'required' => false],
                    'publik' => ['tipe' => 'boolean', 'required' => false],
                ]),
                'urutan' => 1,
            ]
        );

        $this->info("ObjekLapanganType 'RUMAH' (id={$rumahType->id}) siap.");
        $this->newLine();

        // 2. Hitung total Rumah
        $totalRumah = Rumah::count();
        $alreadyMigrated = DB::table('objek_lapangans')
            ->where('objek_lapangan_type_id', $rumahType->id)
            ->whereNotNull('kode')
            ->where('kode', 'like', 'RUMAH-%')
            ->count();

        $this->info("Total Rumah: $totalRumah");
        $this->info("Sudah termigrasi (dengan kode RUMAH-*): $alreadyMigrated");
        $this->newLine();

        $rumah = Rumah::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('id')
            ->cursor();

        $bar = $this->output->createProgressBar($totalRumah);
        $bar->start();

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($rumah as $r) {
            $bar->advance();

            // Skip jika sudah ada ObjekLapangan dengan kode yang sama
            $existing = ObjekLapangan::where('objek_lapangan_type_id', $rumahType->id)
                ->where('kode', 'RUMAH-' . $r->id)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $created++;
                continue;
            }

            try {
                DB::transaction(function () use ($r, $rumahType) {
                    ObjekLapangan::create([
                        'objek_lapangan_type_id' => $rumahType->id,
                        'desa_id' => $r->desa_id,
                        'dusun_id' => $r->dusun_id,
                        'nama' => null, // Rumah tidak punya nama individual
                        'kode' => 'RUMAH-' . $r->id, // reference ke rumah.id
                        'alamat' => $r->alamat_lengkap,
                        'koordinat_lat' => $r->latitude,
                        'koordinat_lng' => $r->longitude,
                        'geometry_wkt' => null,
                        'attributes' => [
                            'kode_rumah' => $r->kode_rumah,
                            'status_rumah' => $r->status_rumah,
                            'keterangan' => $r->keterangan,
                            'publik' => (bool) $r->publik,
                            // backward-compat: reference ke Rumah lama
                            'legacy_rumah_id' => $r->id,
                        ],
                        'status_verifikasi' => $r->deleted_at ? 'draft' : 'diverifikasi',
                        'verified_at' => $r->updated_at,
                        'verified_by' => $r->created_by, // best guess
                        'source_attribution_id' => $r->source_attribution_id,
                        'created_by' => $r->created_by,
                        'updated_by' => $r->created_by,
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,
                    ]);
                });

                $created++;
            } catch (Throwable $e) {
                $errors++;
                $this->error("Rumah id={$r->id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();

        $this->info('========================================');
        $this->info('SUMMARY');
        $this->info('========================================');
        $this->info("Total Rumah:           $totalRumah");
        $this->info("Created (akan dibuat): $created");
        $this->info("Skipped (sudah ada):   $skipped");
        $this->info("Errors:                $errors");

        if ($dryRun) {
            $this->newLine();
            $this->warn('DRY RUN. Jalankan tanpa --dry-run untuk eksekusi.');
        } else {
            $this->newLine();
            $this->info('Sekarang ObjekLapangan sudah terisi. Tabel rumah TIDAK di-drop (backward compat).');
            $this->info('Untuk membuat Rumah::objekLapangan() relasi, edit model Rumah.');
        }

        return self::SUCCESS;
    }
}
