<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates a repeatable, production-shaped dataset for all Sangihe villages.
 * Names and codes are read from the BIG administrative export in public/data.
 */
class SangiheTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $source = public_path('data/sangihe-desa.json');
        if (! is_file($source)) {
            $this->command?->error('Missing public/data/sangihe-desa.json. Download the BIG village attributes first.');
            return;
        }

        $features = json_decode((string) file_get_contents($source), true, 512, JSON_THROW_ON_ERROR)['features'] ?? [];
        $admin = User::query()->whereHas('roles', fn ($q) => $q->whereIn('name', ['super_admin', 'admin']))->first();
        $kabupaten = Kabupaten::firstOrCreate(['nama' => 'Kabupaten Kepulauan Sangihe']);
        $faker = \Faker\Factory::create('id_ID');
        $created = 0;

        DB::transaction(function () use ($features, $kabupaten, $admin, $faker, &$created): void {
            $byKecamatan = collect($features)->map(fn (array $feature) => $feature['attributes'] ?? [])->filter(fn (array $a) => ! empty($a['WADMKC']) && ! empty($a['WADMKD']))->groupBy('WADMKC');

            foreach ($byKecamatan as $kecamatanName => $desaRows) {
                $kecamatan = Kecamatan::firstOrCreate(['kabupaten_id' => $kabupaten->id, 'nama' => $kecamatanName]);
                $batch = SubmissionBatch::firstOrCreate(
                    ['submission_number' => 'DUMMY-SANGIHE-'.$kecamatan->id.'-2026'],
                    ['module' => SubmissionBatch::MODULE_MIXED, 'kecamatan_id' => $kecamatan->id, 'periode_bulan' => now()->month, 'periode_tahun' => now()->year, 'semester' => now()->month <= 6 ? 'ganjil' : 'genap', 'package_signature' => 'dummy-sangihe-'.$kecamatan->id, 'package_keys' => [SubmissionBatch::MODULE_RUMAH, SubmissionBatch::MODULE_KELUARGA, SubmissionBatch::MODULE_PENDUDUK], 'status' => SubmissionBatch::STATUS_APPROVED, 'notes' => 'Data dummy pengujian SIPANDU.', 'created_by' => $admin?->id, 'submitted_at' => now(), 'verified_at' => now(), 'verified_by' => $admin?->id, 'version' => 1]
                );

                // Kecamatan-level records power the kabupaten comparison charts.
                foreach (range(5, 0) as $monthOffset) {
                    $period = now()->subMonths($monthOffset);
                    DataKesehatanBulanan::firstOrCreate(['desa_id' => null, 'kecamatan_id' => $kecamatan->id, 'periode_bulan' => $period->month, 'periode_tahun' => $period->year], ['puskesmas' => 'Puskesmas '.$kecamatan->nama, 'jumlah_balita' => $faker->numberBetween(80, 260), 'jumlah_ibu_hamil' => $faker->numberBetween(15, 70), 'stunting' => $faker->numberBetween(2, 28), 'gizi_buruk' => $faker->numberBetween(0, 8), 'disabilitas' => $faker->numberBetween(2, 18), 'kematian' => $faker->numberBetween(0, 3), 'kelahiran' => $faker->numberBetween(5, 30), 'bpjs_aktif' => $faker->numberBetween(250, 800), 'bpjs_tidak_aktif' => $faker->numberBetween(20, 100), 'status' => 'Terverifikasi', 'created_by' => $admin?->id]);
                    DataPendidikanBulanan::firstOrCreate(['desa_id' => null, 'kecamatan_id' => $kecamatan->id, 'periode_bulan' => $period->month, 'periode_tahun' => $period->year], ['sekolah' => 'Satuan Pendidikan '.$kecamatan->nama, 'jumlah_siswa' => $faker->numberBetween(400, 1800), 'laki_laki' => $faker->numberBetween(190, 900), 'perempuan' => $faker->numberBetween(190, 900), 'putus_sekolah' => $faker->numberBetween(2, 35), 'lulus' => $faker->numberBetween(40, 250), 'naik_kelas' => $faker->numberBetween(300, 1500), 'tidak_naik' => $faker->numberBetween(2, 35), 'status' => 'Terverifikasi', 'created_by' => $admin?->id]);
                }

                foreach ($desaRows as $desaRow) {
                    $desa = Desa::firstOrCreate(['kecamatan_id' => $kecamatan->id, 'nama' => $desaRow['WADMKD']], ['kode' => $desaRow['KDEBPS'] ?: ($desaRow['KDEPUM'] ?? null)]);
                    for ($houseIndex = 1; $houseIndex <= 2; $houseIndex++) {
                        $house = Rumah::firstOrCreate(['kode_rumah' => sprintf('SGH-%d-%d-%02d', $kecamatan->id, $desa->id, $houseIndex)], ['desa_id' => $desa->id, 'alamat_lengkap' => 'Lingkungan '.$houseIndex.', '.$desa->nama.', '.$kecamatan->nama, 'latitude' => 3.55 + $faker->randomFloat(6, -.16, .16), 'longitude' => 125.48 + $faker->randomFloat(6, -.12, .12), 'status_rumah' => $faker->randomElement(array_keys(Rumah::STATUS)), 'keterangan' => 'Data dummy SIPANDU', 'created_by' => $admin?->id]);
                        $family = Keluarga::firstOrCreate(['nomor_kk' => sprintf('717103%09d', ($desa->id * 10) + $houseIndex)], ['rumah_id' => $house->id, 'nama_kepala_keluarga' => $faker->name, 'jumlah_anggota' => 3, 'status_ekonomi' => $faker->randomElement(array_keys(Keluarga::STATUS_EKONOMI)), 'created_by' => $admin?->id]);
                        for ($member = 1; $member <= 3; $member++) {
                            Penduduk::firstOrCreate(['nik' => sprintf('717103%09d', ($desa->id * 100) + ($houseIndex * 10) + $member)], ['keluarga_id' => $family->id, 'nama_lengkap' => $faker->name, 'jenis_kelamin' => $faker->randomElement(array_keys(Penduduk::JENIS_KELAMIN)), 'tempat_lahir' => $kecamatan->nama, 'tanggal_lahir' => $faker->dateTimeBetween('-70 years', '-2 years')->format('Y-m-d'), 'agama' => $faker->randomElement(array_keys(Penduduk::AGAMA)), 'status_perkawinan' => 'Belum Kawin', 'pendidikan' => $faker->randomElement(array_keys(Penduduk::PENDIDIKAN)), 'pekerjaan' => 'Pelajar', 'penghasilan' => 0, 'status_dalam_keluarga' => $member === 1 ? 'Kepala Keluarga' : 'Anak', 'status_penduduk' => 'Tetap', 'created_by' => $admin?->id]);
                        }
                        foreach ([$house, $family, ...$family->penduduk()->get()->all()] as $entity) {
                            SubmissionBatchItem::firstOrCreate(['submission_batch_id' => $batch->id, 'entity_type' => $entity->getMorphClass(), 'entity_id' => $entity->id]);
                        }
                        $created++;
                    }
                    foreach (range(5, 0) as $monthOffset) {
                        $period = now()->subMonths($monthOffset);
                        DataKesehatanBulanan::firstOrCreate(['desa_id' => $desa->id, 'kecamatan_id' => $kecamatan->id, 'periode_bulan' => $period->month, 'periode_tahun' => $period->year], ['puskesmas' => 'Puskesmas '.$kecamatan->nama, 'jumlah_balita' => $faker->numberBetween(8, 35), 'jumlah_ibu_hamil' => $faker->numberBetween(2, 12), 'stunting' => $faker->numberBetween(0, 5), 'gizi_buruk' => $faker->numberBetween(0, 2), 'disabilitas' => $faker->numberBetween(0, 4), 'kematian' => $faker->numberBetween(0, 1), 'kelahiran' => $faker->numberBetween(0, 6), 'bpjs_aktif' => $faker->numberBetween(25, 80), 'bpjs_tidak_aktif' => $faker->numberBetween(2, 15), 'status' => 'Terverifikasi', 'created_by' => $admin?->id]);
                        DataPendidikanBulanan::firstOrCreate(['desa_id' => $desa->id, 'kecamatan_id' => $kecamatan->id, 'periode_bulan' => $period->month, 'periode_tahun' => $period->year], ['sekolah' => 'Sekolah '.$desa->nama, 'jumlah_siswa' => $faker->numberBetween(35, 180), 'laki_laki' => $faker->numberBetween(15, 95), 'perempuan' => $faker->numberBetween(15, 95), 'putus_sekolah' => $faker->numberBetween(0, 6), 'lulus' => $faker->numberBetween(2, 25), 'naik_kelas' => $faker->numberBetween(20, 100), 'tidak_naik' => $faker->numberBetween(0, 8), 'status' => 'Terverifikasi', 'created_by' => $admin?->id]);
                    }
                }
            }
        });

        $this->command?->info("Sangihe test data ready: {$created} rumah across ".count($features).' villages.');
    }
}
