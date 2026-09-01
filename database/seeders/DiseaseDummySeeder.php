<?php

namespace Database\Seeders;

use App\Models\DataKesehatanBulanan;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Menyediakan data contoh kesehatan yang langsung dapat ditampilkan di peta.
 *
 * Setiap data dimasukkan ke batch "disetujui", karena peta hanya membaca
 * laporan kesehatan yang telah disetujui. Seeder ini idempoten dan tidak
 * mengubah laporan kesehatan yang sudah dimasukkan pengguna.
 */
class DiseaseDummySeeder extends Seeder
{
    public function run(): void
    {
        $period = now();
        $admin = User::query()->first();
        $created = 0;

        foreach (Kecamatan::query()->orderBy('id')->get() as $kecamatan) {
            $batch = SubmissionBatch::firstOrCreate(
                ['submission_number' => sprintf('DUMMY-KESEHATAN-%d-%d', $kecamatan->id, $period->year)],
                [
                    'module' => SubmissionBatch::MODULE_KESEHATAN,
                    'kecamatan_id' => $kecamatan->id,
                    'periode_bulan' => $period->month,
                    'periode_tahun' => $period->year,
                    'status' => SubmissionBatch::STATUS_APPROVED,
                    'package_signature' => sprintf('dummy-kesehatan-%d-%d', $kecamatan->id, $period->year),
                    'package_keys' => [SubmissionBatch::MODULE_KESEHATAN],
                    'notes' => 'Data dummy untuk demonstrasi peta kesehatan.',
                    'created_by' => $admin?->id,
                    'submitted_at' => $period,
                    'verified_at' => $period,
                    'verified_by' => $admin?->id,
                ]
            );

            $desaIds = Desa::query()
                ->where('kecamatan_id', $kecamatan->id)
                ->orderBy('id')
                ->pluck('id')
                ->values();

            // Lima unit layanan memberi variasi data pada popup dan statistik peta.
            foreach (range(1, 5) as $index) {
                $desaId = $desaIds->isNotEmpty()
                    ? $desaIds[($index - 1) % $desaIds->count()]
                    : null;

                $row = DataKesehatanBulanan::firstOrCreate(
                    [
                        'kecamatan_id' => $kecamatan->id,
                        'desa_id' => $desaId,
                        'puskesmas' => sprintf('Unit Kesehatan Dummy %d - %s', $index, $kecamatan->nama),
                        'periode_bulan' => $period->month,
                        'periode_tahun' => $period->year,
                    ],
                    [
                        'jenis' => $index === 1 ? 'Puskesmas' : 'Pustu',
                        'jumlah_balita' => 35 + ($index * 9),
                        'jumlah_ibu_hamil' => 8 + ($index * 3),
                        'balita' => 35 + ($index * 9),
                        'ibu_hamil' => 8 + ($index * 3),
                        'bayi' => 10 + ($index * 2),
                        'imunisasi' => 24 + ($index * 5),
                        'kunjungan' => 120 + ($index * 37),
                        'rawat_jalan' => 90 + ($index * 24),
                        'rawat_inap' => 2 + $index,
                        'rujukan' => 1 + ($index % 4),
                        'persalinan' => 1 + ($index % 3),
                        'stunting' => $index % 4,
                        'gizi_buruk' => $index === 5 ? 1 : 0,
                        'ispa' => 8 + ($index * 5),
                        'dbd' => $index % 3,
                        'malaria' => $index % 2,
                        'tbc' => $index % 3,
                        'hipertensi' => 5 + ($index * 3),
                        'diabetes' => 2 + $index,
                        'covid' => $index === 1 ? 1 : 0,
                        'lainnya' => $index % 2,
                        'dokter' => $index === 1 ? 3 : 1,
                        'perawat' => 3 + $index,
                        'bidan' => 2 + $index,
                        'apoteker' => $index === 1 ? 1 : 0,
                        'ahli_gizi' => $index <= 2 ? 1 : 0,
                        'ambulans' => $index === 1,
                        'laboratorium' => $index <= 2,
                        'apotek' => $index <= 2,
                        'ruang_bersalin' => $index <= 3,
                        'internet' => $index !== 5,
                        'listrik' => true,
                        'air_bersih' => true,
                        'status_obat' => $index === 5 ? 'Terbatas' : 'Tersedia',
                        'permasalahan' => $index === 5 ? ['Ketersediaan obat terbatas'] : [],
                        'uraian_permasalahan' => $index === 5 ? 'Stok obat perlu penambahan.' : null,
                        'disabilitas' => $index,
                        'kematian' => 0,
                        'kelahiran' => 1 + ($index % 3),
                        'bpjs_aktif' => 90 + ($index * 30),
                        'bpjs_tidak_aktif' => 8 + ($index * 4),
                        'keterangan' => 'Data dummy — hanya untuk demonstrasi peta kesehatan.',
                        'status' => 'Terverifikasi',
                        'created_by' => $admin?->id,
                    ]
                );

                SubmissionBatchItem::firstOrCreate([
                    'submission_batch_id' => $batch->id,
                    'entity_type' => $row->getMorphClass(),
                    'entity_id' => $row->id,
                ]);

                $created++;
            }
        }

        $this->command?->info("Health dummy data ready: {$created} unit layanan.");
    }
}
