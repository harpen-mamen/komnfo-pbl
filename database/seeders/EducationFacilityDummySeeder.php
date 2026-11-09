<?php

namespace Database\Seeders;

use App\Models\DataPendidikanBulanan;
use Illuminate\Database\Seeder;

class EducationFacilityDummySeeder extends Seeder
{
    public function run(): void
    {
        DataPendidikanBulanan::query()->each(function (DataPendidikanBulanan $row): void {
            $seed = crc32((string) $row->id);
            $row->forceFill([
                'internet' => $seed % 10 !== 0,
                'listrik' => true,
                'air_bersih' => $seed % 7 !== 0,
                'perpustakaan' => $seed % 4 !== 0,
                'laboratorium' => $seed % 3 === 0,
                'uks' => $seed % 5 !== 0,
                'jumlah_ruang' => 5 + ($seed % 16),
                'rusak_ringan' => $seed % 5,
                'rusak_berat' => $seed % 3 === 0 ? 1 : 0,
            ])->saveQuietly();
        });
    }
}
