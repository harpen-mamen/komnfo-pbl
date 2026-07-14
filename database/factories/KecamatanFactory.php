<?php

namespace Database\Factories;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Factories\Factory;

class KecamatanFactory extends Factory
{
    protected $model = Kecamatan::class;

    public function definition(): array
    {
        return [
            'kabupaten_id' => Kabupaten::factory(),
            'nama' => $this->faker->city . ' Kecamatan',
        ];
    }
}
