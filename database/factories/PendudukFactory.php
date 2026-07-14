<?php

namespace Database\Factories;

use App\Models\Penduduk;
use Illuminate\Database\Eloquent\Factories\Factory;

class PendudukFactory extends Factory
{
    protected $model = Penduduk::class;

    public function definition(): array
    {
        return [
            'keluarga_id' => null,
            'nik' => $this->faker->unique()->numerify('################'),
            'nama_lengkap' => $this->faker->name,
            'pendidikan' => 'SD',
            'pekerjaan' => 'Tidak bekerja',
        ];
    }
}
