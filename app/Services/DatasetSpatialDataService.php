<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetValue;
use Illuminate\Support\Collection;

/** Builds GeoJSON layers from published dataset values with a geometry field. */
class DatasetSpatialDataService
{
    /** Only intentionally public point layers are exposed; future GIS datasets need review before addition. */
    private const PUBLIC_POINT_LAYERS = [
        'LOKASI_TRANSPORTASI_PUBLIK' => 'DISHUB',
        'LOKASI_FASKES_PUBLIK' => 'DINKES',
    ];

    public function featuresForDataset(Dataset $dataset, ?Collection $records = null): array
    {
        $fields = $dataset->relationLoaded('fields') ? $dataset->fields : $dataset->fields()->get();
        if (! $dataset->is_active || ! in_array($dataset->lisensi, [Dataset::LISENSI_CCBY, Dataset::LISENSI_OPEN], true)
            || (self::PUBLIC_POINT_LAYERS[$dataset->kode] ?? null) !== $dataset->opd?->kode) return [];

        $geometryFields = $fields->filter(fn (DatasetField $field) => $this->isGeometryField($field));
        if ($geometryFields->isEmpty()) return [];

        $records ??= $dataset->values()
            ->where('status', DatasetValue::STATUS_PUBLISHED)
            ->whereNotNull('published_at')->get();

        $features = [];
        foreach ($records as $record) {
            if ($record instanceof DatasetValue && ($record->status !== DatasetValue::STATUS_PUBLISHED || ! $record->published_at
                || str_starts_with((string) $record->notes, '[DEMO-'))) continue;

            foreach ($geometryFields as $field) {
                $geometryValue = data_get($record->values, $field->nama);
                $geometry = $this->normalizeGeometry($geometryValue);
                if (! $geometry) continue;

                $attributes = collect($fields)
                    ->filter(fn (DatasetField $item) => in_array($item->nama, ['nama', 'jenis'], true))
                    ->mapWithKeys(fn (DatasetField $item) => [$item->label ?: $item->nama => data_get($record->values, $item->nama)])
                    ->filter(fn ($value) => is_scalar($value) && $value !== '')
                    ->take(10)->all();
                $name = $this->featureName($record, $fields, $dataset);
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => $geometry,
                    'properties' => [
                        'name' => $name,
                        'dataset' => $dataset->nama,
                        'dataset_code' => $dataset->kode_sdi ?: $dataset->kode,
                        'opd' => $dataset->opd?->nama ?? 'OPD',
                        'year' => $record->tahun,
                        'is_demo' => false,
                        'attributes' => $attributes,
                    ],
                ];
            }
        }

        return $features;
    }

    /** Build the published, open-data map layer across active datasets. */
    public function publishedFeatureCollection(): array
    {
        $datasets = Dataset::query()
            ->where('is_active', true)
            ->whereIn('lisensi', [Dataset::LISENSI_CCBY, Dataset::LISENSI_OPEN])
            ->where(function ($query): void {
                foreach (self::PUBLIC_POINT_LAYERS as $code => $owner) {
                    $query->orWhere(fn ($pair) => $pair->where('kode', $code)->whereHas('opd', fn ($opd) => $opd->where('kode', $owner)));
                }
            })
            ->whereHas('fields', fn ($query) => $query->where('tipe', 'json')->where('lookup_source', 'like', 'geometry:%'))
            ->with(['opd', 'fields', 'values' => fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('published_at')
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))])
            ->get();

        $features = $datasets->flatMap(fn (Dataset $dataset) => $this->featuresForDataset($dataset, $dataset->values))->values()->all();

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    /** Admin map displays only published data with real submitted geometry. */
    public function mapFeatureCollectionForAdmin(): array
    {
        return $this->publishedFeatureCollection();
    }

    private function isGeometryField(DatasetField $field): bool
    {
        return $field->tipe === 'json' && str_starts_with((string) $field->lookup_source, 'geometry:');
    }

    private function normalizeGeometry(mixed $value): ?array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (! is_array($value)) return null;

        $geometry = ($value['type'] ?? null) === 'Feature' ? ($value['geometry'] ?? null) : $value;
        if (! is_array($geometry) || ! self::geometryValid($geometry)) return null;
        return $geometry;
    }

    private static function geometryValid(array $geometry): bool
    {
        return GisGeometry::isValid($geometry);
    }

    private function featureName(DatasetValue|DatasetSpatialDemo $record, Collection $fields, Dataset $dataset): string
    {
        foreach (['nama_ruas', 'nama_jembatan', 'nama', 'lokasi', 'ruas', 'sumber', 'jenis'] as $key) {
            $field = $fields->firstWhere('nama', $key);
            $value = $field ? data_get($record->values, $field->nama) : null;
            if (is_scalar($value) && trim((string) $value) !== '') return (string) $value;
        }

        return $dataset->nama;
    }
}
