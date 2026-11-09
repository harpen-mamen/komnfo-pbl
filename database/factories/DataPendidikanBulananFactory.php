<?php

namespace Database\Factories;

use App\Models\DataPendidikanBulanan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataPendidikanBulananFactory extends Factory
{
    protected $model = DataPendidikanBulanan::class;

    public function definition(): array
    {
        return [
            'kecamatan_id' => null,
            'desa_id' => null,
            'sekolah' => $this->faker->company,
            'periode_bulan' => $this->faker->numberBetween(1, 12),
            'periode_tahun' => now()->year,
            'jumlah_siswa' => $this->faker->numberBetween(0, 500),
        ];
    }
}
