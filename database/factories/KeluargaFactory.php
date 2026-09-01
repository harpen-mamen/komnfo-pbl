<?php

namespace Database\Factories;

use App\Models\Keluarga;
use Illuminate\Database\Eloquent\Factories\Factory;

class KeluargaFactory extends Factory
{
    protected $model = Keluarga::class;

    public function definition(): array
    {
        return [
            'rumah_id' => null,
            'nomor_kk' => $this->faker->unique()->numerify('############'),
            'nama_kepala_keluarga' => $this->faker->name,
            'jumlah_anggota' => $this->faker->numberBetween(1,6),
            'status_ekonomi' => $this->faker->randomElement(array_keys(Keluarga::STATUS_EKONOMI)),
        ];
    }
}
