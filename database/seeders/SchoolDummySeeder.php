<?php

namespace Database\Seeders;

use App\Models\DataPendidikanBulanan;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolDummySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->first();
        $period = now();
        $created = 0;
        foreach (Kecamatan::query()->orderBy('id')->get() as $kecamatan) {
            $batch = SubmissionBatch::firstOrCreate(
                ['submission_number' => 'DUMMY-PENDIDIKAN-'.$kecamatan->id.'-2026'],
                ['module' => SubmissionBatch::MODULE_PENDIDIKAN, 'kecamatan_id' => $kecamatan->id, 'periode_bulan' => $period->month, 'periode_tahun' => $period->year, 'semester' => $period->month <= 6 ? 'genap' : 'ganjil', 'status' => SubmissionBatch::STATUS_APPROVED, 'package_signature' => 'dummy-school-'.$kecamatan->id, 'package_keys' => [SubmissionBatch::MODULE_PENDIDIKAN], 'created_by' => $admin?->id, 'submitted_at' => $period, 'verified_at' => $period, 'verified_by' => $admin?->id]
            );
            $desaIds = Desa::query()->where('kecamatan_id', $kecamatan->id)->pluck('id')->values();
            foreach (range(1, 5) as $index) {
                $desaId = $desaIds->isNotEmpty() ? $desaIds[($index - 1) % $desaIds->count()] : null;
                $row = DataPendidikanBulanan::firstOrCreate(['kecamatan_id' => $kecamatan->id, 'desa_id' => $desaId, 'sekolah' => 'Sekolah Dummy '.$index.' '.$kecamatan->nama, 'periode_bulan' => $period->month, 'periode_tahun' => $period->year], [
                    'tahun_ajaran' => $period->year.'/'.($period->year + 1), 'semester' => $period->month <= 6 ? 'Genap' : 'Ganjil', 'npsn' => 'DUMMY'.str_pad((string) ($kecamatan->id * 100 + $index), 8, '0', STR_PAD_LEFT), 'status_sekolah' => $index % 4 === 0 ? 'Swasta' : 'Negeri', 'jenjang' => ['SD', 'SMP', 'SMA', 'SD', 'TK'][$index - 1], 'jumlah_siswa' => 80 + ($index * 35), 'laki_laki' => 40 + ($index * 16), 'perempuan' => 40 + ($index * 19), 'rombel' => 6 + $index, 'siswa_baru' => 10 + $index, 'siswa_lulus' => 12 + $index, 'putus_sekolah' => $index % 3, 'guru_asn' => 4 + $index, 'guru_pppk' => 2, 'guru_honorer' => 2 + $index, 'tenaga_administrasi' => 1 + ($index % 2), 'kepala_sekolah' => 1, 'internet' => $index !== 5, 'listrik' => true, 'air_bersih' => $index !== 4, 'perpustakaan' => $index !== 5, 'laboratorium' => in_array($index, [2, 3], true), 'uks' => $index !== 4, 'jumlah_ruang' => 8 + $index, 'rusak_ringan' => $index % 3, 'rusak_berat' => $index === 5 ? 1 : 0, 'status' => 'Terverifikasi', 'created_by' => $admin?->id
                ]);
                SubmissionBatchItem::firstOrCreate(['submission_batch_id' => $batch->id, 'entity_type' => $row->getMorphClass(), 'entity_id' => $row->id]);
                $created++;
            }
        }
        $this->command?->info("School dummy data ready: {$created} schools.");
    }
}
