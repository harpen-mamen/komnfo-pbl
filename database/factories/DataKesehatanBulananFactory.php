<?php

namespace Database\Factories;

use App\Models\DataKesehatanBulanan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataKesehatanBulananFactory extends Factory
{
    protected $model = DataKesehatanBulanan::class;

    public function definition(): array
    {
        return [
            'kecamatan_id' => null,
            'desa_id' => null,
            'puskesmas' => $this->faker->company,
            'periode_bulan' => $this->faker->numberBetween(1, 12),
            'periode_tahun' => now()->year,
            'jumlah_balita' => $this->faker->numberBetween(0, 200),
        ];
    }
}
