<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetSpatialDemo;
use App\Models\DatasetValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Legacy synthetic map samples. Not included in DatabaseSeeder; use only for isolated previews. */
class DatasetSpatialDemoDataSeeder extends Seeder
{
    private const MARKER = '[DEMO-PETA]';

    public function run(): void
    {
        $this->command?->warn('Seeder fitur peta sintetis dinonaktifkan; peta hanya menampilkan geometri dari data OPD terbit.');
    }

    private function geometryFor(string $type, int $index): array
    {
        $longitude = 125.54 + (($index % 9) * 0.012);
        $latitude = 3.58 + (($index % 7) * 0.009);

        $coordinates = match ($type) {
            'linestring' => [[$longitude - 0.015, $latitude - 0.008], [$longitude, $latitude], [$longitude + 0.018, $latitude + 0.012]],
            'polygon' => [[[$longitude - 0.012, $latitude - 0.01], [$longitude + 0.012, $latitude - 0.01], [$longitude + 0.012, $latitude + 0.01], [$longitude - 0.012, $latitude + 0.01], [$longitude - 0.012, $latitude - 0.01]]],
            default => [$longitude, $latitude],
        };
        $geoType = match ($type) {
            'linestring' => 'LineString',
            'polygon' => 'Polygon',
            default => 'Point',
        };

        return [
            'type' => 'Feature',
            'geometry' => ['type' => $geoType, 'coordinates' => $coordinates],
            'properties' => ['is_demo' => true],
        ];
    }

    private function sampleValue(DatasetField $field, Dataset $dataset, int $index): mixed
    {
        if ($field->tipe === 'enum') {
            $options = $field->options ?? [];
            if (is_string($options)) $options = json_decode($options, true) ?: [];
            return $options === [] ? 'Contoh demo' : array_key_first($options);
        }

        return match ($field->tipe) {
            'integer' => 1 + ($index % 9),
            'decimal' => round(1.5 + ($index % 5) * 0.75, 2),
            'boolean' => true,
            'date' => now()->toDateString(),
            'datetime' => now()->toDateTimeString(),
            'json' => null,
            'reference' => 'DEMO-' . ($index + 1),
            default => $this->sampleText($field, $dataset, $index),
        };
    }

    private function sampleText(DatasetField $field, Dataset $dataset, int $index): string
    {
        $name = strtolower($field->nama . ' ' . $field->label);
        if (str_contains($name, 'tahun')) return (string) now()->year;
        if (str_contains($name, 'kecamatan')) return ['Tahuna', 'Tamako', 'Manganitu'][$index % 3];
        if (preg_match('/nama|ruas|jembatan|sarana|fasilitas|destinasi|pasar|pelabuhan|lokasi/', $name)) {
            return 'Contoh ' . ($field->label ?: $field->nama) . ' ' . ($index + 1);
        }

        return 'Contoh demo ' . ($field->label ?: $field->nama);
    }
}
