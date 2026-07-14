<?php

namespace Database\Factories;

use App\Models\Kabupaten;
use Illuminate\Database\Eloquent\Factories\Factory;

class KabupatenFactory extends Factory
{
    protected $model = Kabupaten::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->city . ' Kabupaten',
        ];
    }
}
