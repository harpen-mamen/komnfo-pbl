<?php

namespace Database\Factories;

use App\Models\Rumah;
use Illuminate\Database\Eloquent\Factories\Factory;

class RumahFactory extends Factory
{
    protected $model = Rumah::class;

    public function definition(): array
    {
        return [
            'desa_id' => null,
            'kode_rumah' => $this->faker->unique()->bothify('R-####'),
            'alamat_lengkap' => $this->faker->address,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'status_rumah' => $this->faker->randomElement(array_keys(Rumah::STATUS)),
        ];
    }
}
