<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyWilayahSeeder extends Seeder
{
    public function run(): void
    {
        $kabupaten = Kabupaten::firstOrCreate([
            'nama' => 'Kabupaten Kepulauan Sangihe',
        ]);

        $wilayah = [
            'Tahuna' => ['Soataloara I', 'Soataloara II'],
            'Tahuna Barat' => ['Kapitu', 'Lihunu'],
            'Tahuna Timur' => ['Rap-rap', 'Sengkol'],
            'Tamako' => ['Mawali', 'Bailang'],
            'Tabukan Selatan' => ['Sarulowu', 'Kamarang'],
            'Tabukan Selatan Tenggara' => ['Saeng', 'Linama'],
            'Tabukan Tengah' => ['Pasiolang', 'Makawidey'],
            'Tabukan Utara' => ['Totabuan', 'Tuminting'],
            'Nusa Tabukan' => ['Baholongan', 'Natuna'],
            'Kendahe' => ['Lapodese', 'Kaweng'],
            'Manganitu' => ['Ragang', 'Pangha'],
            'Manganitu Selatan' => ['Peta', 'Noongan'],
            'Kepulauan Marore' => ['Marore Besar', 'Marore Kecil'],
            'Tatoareng' => ['Kahakitang', 'Para'],
        ];

        $faker = \Faker\Factory::create('id_ID');
        $desaCodes = 1;
        $createdById = User::firstWhere('email', 'superadmin@sipdt.test')?->id;

        $kecamatanModels = [];
        foreach ($wilayah as $kecamatanName => $desaNames) {
            $kecamatan = Kecamatan::firstOrCreate([
                'kabupaten_id' => $kabupaten->id,
                'nama' => $kecamatanName,
            ]);

            $kecamatanModels[] = $kecamatan;

            foreach ($desaNames as $desaName) {
                Desa::firstOrCreate([
                    'kecamatan_id' => $kecamatan->id,
                    'nama' => $desaName,
                ], [
                    'kode' => sprintf('717101%04d', $desaCodes++),
                ]);
            }
        }

        $targetKecamatan = $kecamatanModels[0];
        $targetDesa = Desa::firstOrCreate([
            'kecamatan_id' => $targetKecamatan->id,
            'nama' => 'Soataloara I',
        ], [
            'kode' => '7171012001',
        ]);

        $houseCount = 30;
        $totalPenduduk = 50;
        $remainingPenduduk = $totalPenduduk;
        $keluargaRecords = [];

        $baseLat = 3.6235;
        $baseLng = 125.5020;

        for ($i = 1; $i <= $houseCount; $i++) {
            $houseLat = $baseLat + (($i % 6) * 0.0012) + ($faker->randomFloat(7, -0.0003, 0.0003));
            $houseLng = $baseLng + ((int) ($i / 6) * 0.0015) + ($faker->randomFloat(7, -0.0003, 0.0003));
            $kodeRumah = sprintf('SGH-SOA-%04d', $i);

            $rumah = Rumah::firstOrCreate([
                'desa_id' => $targetDesa->id,
                'kode_rumah' => $kodeRumah,
            ], [
                'alamat_lengkap' => "Jalan Soataloara No. {$i}, Desa Soataloara I, Kecamatan Tahuna",
                'latitude' => $houseLat,
                'longitude' => $houseLng,
                'status_rumah' => $faker->randomElement(array_keys(Rumah::STATUS)),
                'keterangan' => 'Rumah dummy untuk pengujian data sistem.',
                'created_by' => $createdById,
            ]);

            $minFamilyMembers = 1;
            $maxAvailable = max(1, $remainingPenduduk - ($houseCount - $i));
            $familySize = $faker->numberBetween($minFamilyMembers, min(5, $maxAvailable));
            if ($i === $houseCount) {
                $familySize = $remainingPenduduk;
            }
            $remainingPenduduk -= $familySize;

            $keluarga = Keluarga::firstOrCreate([
                'rumah_id' => $rumah->id,
                'nomor_kk' => sprintf('71710101%08d', $i),
            ], [
                'nama_kepala_keluarga' => $faker->name,
                'jumlah_anggota' => $familySize,
                'status_ekonomi' => $faker->randomElement(array_keys(Keluarga::STATUS_EKONOMI)),
                'created_by' => $createdById,
            ]);

            $keluargaRecords[] = ['keluarga' => $keluarga, 'size' => $familySize];
        }

        $familyRoleOptions = ['Istri', 'Anak', 'Menantu', 'Cucu', 'Orang Tua', 'Lainnya'];
        $pendudukCount = 0;

        foreach ($keluargaRecords as $index => $record) {
            $keluarga = $record['keluarga'];
            $familySize = $record['size'];

            for ($memberIndex = 1; $memberIndex <= $familySize; $memberIndex++) {
                $isHead = $memberIndex === 1;
                $birthDate = $faker->dateTimeBetween('-65 years', '-1 years')->format('Y-m-d');
                $age = (int) date('Y') - (int) date('Y', strtotime($birthDate));
                $gender = $faker->randomElement(['Laki-laki', 'Perempuan']);
                $statusPerkawinan = $age >= 18 ? $faker->randomElement(['Kawin', 'Belum Kawin']) : 'Belum Kawin';
                $statusDalamKeluarga = $isHead ? 'Kepala Keluarga' : $faker->randomElement($familyRoleOptions);
                $pendidikanOptions = array_keys(Penduduk::PENDIDIKAN);
                $penghasilan = $age >= 18 ? $faker->numberBetween(0, 7000000) : 0;

                Penduduk::firstOrCreate([
                    'keluarga_id' => $keluarga->id,
                    'nik' => sprintf('71710101%08d', $pendudukCount + 1),
                ], [
                    'nama_lengkap' => $faker->name($gender === 'Laki-laki' ? 'male' : 'female'),
                    'jenis_kelamin' => $gender,
                    'tempat_lahir' => 'Tahuna',
                    'tanggal_lahir' => $birthDate,
                    'agama' => $faker->randomElement(array_keys(Penduduk::AGAMA)),
                    'status_perkawinan' => $statusPerkawinan,
                    'pendidikan' => $faker->randomElement($pendidikanOptions),
                    'pekerjaan' => $age >= 17 ? $faker->randomElement(['Petani', 'Nelayan', 'Pedagang', 'Guru', 'PNS', 'BUMN', 'Wiraswasta', 'Pelajar']) : 'Pelajar',
                    'penghasilan' => $penghasilan,
                    'status_dalam_keluarga' => $statusDalamKeluarga,
                    'nomor_hp' => $faker->numerify('08##########'),
                    'status_penduduk' => 'Tetap',
                    'created_by' => $createdById,
                ]);

                $pendudukCount++;
                if ($pendudukCount >= $totalPenduduk) {
                    break 2;
                }
            }
        }

        $batch = SubmissionBatch::firstOrCreate([
            'submission_number' => 'DUMMY-' . now()->format('YmdHis'),
        ], [
            'kecamatan_id' => $targetKecamatan->id,
            'semester' => 'Genap',
            'tahun' => (int) now()->format('Y'),
            'status' => SubmissionBatch::STATUS_APPROVED,
            'notes' => 'Batch dummy data wilayah dan penduduk.',
            'created_by' => $createdById,
            'submitted_at' => now(),
            'verified_at' => now(),
            'verified_by' => $createdById,
        ]);

        $entities = collect([]);
        Rumah::query()->where('desa_id', $targetDesa->id)->each(fn (Rumah $rumah) => $entities->push($rumah));
        Keluarga::query()->whereHas('rumah', fn ($query) => $query->where('desa_id', $targetDesa->id))->each(fn (Keluarga $keluarga) => $entities->push($keluarga));
        Penduduk::query()->whereHas('keluarga.rumah', fn ($query) => $query->where('desa_id', $targetDesa->id))->each(fn (Penduduk $penduduk) => $entities->push($penduduk));

        $entities->each(function ($entity) use ($batch) {
            SubmissionBatchItem::firstOrCreate([
                'submission_batch_id' => $batch->id,
                'entity_type' => $entity->getMorphClass(),
                'entity_id' => $entity->id,
            ]);
        });
    }
}
