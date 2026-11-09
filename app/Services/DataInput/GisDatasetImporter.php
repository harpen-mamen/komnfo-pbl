<?php

namespace App\Services\DataInput;

use JsonException;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Wilayah;
use App\Services\GisGeometry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GisDatasetImporter
{
    public function parseGeoJson(string $json): array
    {
        if (strlen($json) > 5 * 1024 * 1024) throw ValidationException::withMessages(['data.payload' => 'Data hasil konversi melebihi batas 5 MB.']);
        try { $document = json_decode($json, true, 128, JSON_THROW_ON_ERROR); }
        catch (JsonException) { throw ValidationException::withMessages(['data.payload' => 'GeoJSON tidak dapat dibaca.']); }
        if (isset($document['crs']) && ! str_contains(strtolower(json_encode($document['crs'])), '4326') && ! str_contains(strtolower(json_encode($document['crs'])), 'crs84')) {
            throw ValidationException::withMessages(['data.payload' => 'Sistem koordinat harus WGS84 / EPSG:4326. Sertakan file .prj pada ZIP SHP agar konversi dapat dilakukan.']);
        }
        if (($document['type'] ?? null) === 'Feature') $features = [$document];
        elseif (($document['type'] ?? null) === 'FeatureCollection' && is_array($document['features'] ?? null)) $features = $document['features'];
        else throw ValidationException::withMessages(['data.payload' => 'File harus berisi GeoJSON Feature atau FeatureCollection.']);
        if (count($features) < 1 || count($features) > 500) throw ValidationException::withMessages(['data.payload' => 'Jumlah fitur harus antara 1 sampai 500.']);
        foreach ($features as $index => $feature) {
            if (($feature['type'] ?? null) !== 'Feature' || (! is_array($feature['properties'] ?? null) && ($feature['properties'] ?? null) !== null) || ! GisGeometry::isValid($feature['geometry'] ?? null)) {
                throw ValidationException::withMessages(['data.payload' => 'Fitur ' . ($index + 1) . ' memiliki geometri atau atribut yang tidak valid. Pastikan koordinat WGS84, garis memiliki 2 titik, dan poligon tertutup.']);
            }
            if (($feature['properties'] ?? null) === null) $features[$index]['properties'] = [];
        }
        return $features;
    }

    public function import(Dataset $dataset, array $state): int
    {
        abort_unless($dataset->is_active, 404);
        $actor = Auth::user();
        abort_unless($actor && ($actor->isSuperAdmin() || (int) $actor->opd_id === (int) $dataset->opd_id), 403);
        $geometryField = $dataset->fields->first(fn ($field) => $field->tipe === 'json' && str_starts_with((string) $field->lookup_source, 'geometry:'));
        if (! $geometryField) throw ValidationException::withMessages(['data.payload' => 'Dataset ini belum memiliki field geometri.']);
        $expected = substr((string) $geometryField->lookup_source, strlen('geometry:')) ?: 'any';
        $features = $this->parseGeoJson((string) ($state['payload'] ?? ''));
        $year = (int) ($state['tahun'] ?? now()->year);
        $period = $state['periode_label'] ?? null;
        $wilayahId = $state['wilayah_id'] ?? null;
        $wilayah = $wilayahId ? Wilayah::query()->where('aktif', true)->find($wilayahId) : null;
        if ($wilayahId && ! $wilayah) throw ValidationException::withMessages(['data.wilayah_id' => 'Wilayah tidak aktif atau tidak ditemukan.']);
        $wilayahType = $wilayah?->tipe ?? 'kabupaten';
        $defaults = is_array($state['defaults'] ?? null) ? $state['defaults'] : [];
        $mapping = is_array($state['mapping'] ?? null) ? $state['mapping'] : [];
        foreach ($features as $i => $feature) {
            if (! GisGeometry::isValid($feature['geometry'] ?? null, $expected)) {
                throw ValidationException::withMessages(['data.payload' => 'Tipe geometri fitur ' . ($i + 1) . ' tidak sesuai dengan dataset (' . $expected . ').']);
            }
        }
        return DB::transaction(function () use ($dataset, $features, $year, $period, $wilayahId, $wilayahType, $defaults, $mapping, $geometryField) {
            foreach ($features as $index => $feature) {
                $values = [];
                foreach ($dataset->fields as $field) {
                    $name = $field->nama;
                    if ($name === $geometryField->nama || $name === 'tahun') continue;
                    if (array_key_exists($name, $defaults) && $defaults[$name] !== '' && $defaults[$name] !== null) $values[$name] = $defaults[$name];
                    $property = $mapping[$name] ?? null;
                    if (is_string($property) && $property !== '' && array_key_exists($property, $feature['properties'])) $values[$name] = $feature['properties'][$property];
                }
                $values[$geometryField->nama] = ['type' => 'Feature', 'geometry' => $feature['geometry'], 'properties' => $feature['properties']];
                try {
                    app(DatasetValueManager::class)->createDraft($dataset, [
                        'tahun' => $year, 'periode_label' => $period,
                        'wilayah_type' => $wilayahType, 'wilayah_id' => $wilayahId,
                        'values' => $values,
                    ]);
                } catch (ValidationException $exception) {
                    $errors = collect($exception->errors())->flatten()->take(4)->implode(' ');
                    throw ValidationException::withMessages(['data.payload' => 'Fitur ' . ($index + 1) . ': ' . $errors]);
                }
            }
            return count($features);
        });
    }
}
