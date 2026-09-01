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
            'jenis_kelamin' => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'tempat_lahir' => 'Tahuna',
            'tanggal_lahir' => '1990-01-01',
            'agama' => 'Kristen',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SD',
            'pekerjaan' => 'Tidak bekerja',
            'status_dalam_keluarga' => 'Anak',
            'status_penduduk' => 'Tetap',
        ];
    }
}
