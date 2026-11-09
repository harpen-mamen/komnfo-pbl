<?php

namespace Database\Seeders;

use App\Models\Rumah;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class CorrectDummyHouseCoordinatesSeeder extends Seeder
{
    public function run(): void
    {
        $geo = json_decode(file_get_contents(public_path('data/batas_wilayah.geojson')), true);
        $centroids = [];
        foreach (($geo['features'] ?? []) as $feature) {
            $name = strtolower(trim($feature['properties']['WADMKC'] ?? $feature['properties']['NAMOBJ'] ?? ''));
            $points = [];
            $collect = function ($value) use (&$collect, &$points): void {
                if (is_array($value) && count($value) >= 2 && is_numeric($value[0]) && is_numeric($value[1])) {
                    $points[] = $value;
                    return;
                }
                foreach ($value as $child) {
                    if (is_array($child)) {
                        $collect($child);
                    }
                }
            };
            $collect($feature['geometry']['coordinates'] ?? []);
            if ($points) {
                $centroids[$name] = ['lat' => array_sum(array_column($points, 1)) / count($points), 'lng' => array_sum(array_column($points, 0)) / count($points)];
            }
        }
        Kecamatan::query()->with('desas')->each(function (Kecamatan $kecamatan) use ($centroids): void {
            $center = $centroids[strtolower(trim($kecamatan->nama))] ?? null;
            if (! $center) {
                return;
            }
            Rumah::query()->whereHas('desa', fn ($q) => $q->where('kecamatan_id', $kecamatan->id))->where('keterangan', 'like', '%dummy%')->get()->each(function (Rumah $house) use ($center): void {
                $house->forceFill(['latitude' => $center['lat'] + (fmod($house->id * 0.00017, 0.006) - 0.003), 'longitude' => $center['lng'] + (fmod($house->id * 0.00013, 0.006) - 0.003)])->saveQuietly();
            });
        });
    }
}
