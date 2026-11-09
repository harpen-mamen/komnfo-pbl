<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\DatasetField;
use Illuminate\Database\Seeder;

/** Add optional map-drawing fields to location-oriented datasets across OPDs. */
class DatasetSpatialFieldsSeeder extends Seeder
{
    private const LOCATION_TERMS = '/lokasi|koordinat|geometri|ruas|jalan|jembatan|sarana|fasilitas|destinasi|wisata|pasar|pelabuhan|puskesmas|sekolah|terminal|kantor|pos_|pulau|menara|irigasi|sungai|drainase|tpi|objek_lapangan/i';

    public function run(): void
    {
        $datasets = Dataset::query()->where('is_active', true)->with('fields')->get();
        $added = 0;

        foreach ($datasets as $dataset) {
            $hasGeometry = $dataset->fields->contains(fn (DatasetField $field) =>
                $field->tipe === 'json' && str_starts_with((string) $field->lookup_source, 'geometry:')
            );
            if ($hasGeometry || ! $this->isLocationDataset($dataset)) continue;

            $name = 'geometri_peta';
            $existing = $dataset->fields()->where('nama', $name)->first();
            DatasetField::query()->updateOrCreate(
                ['dataset_id' => $dataset->id, 'nama' => $name],
                [
                    'label' => $this->geometryLabel($dataset),
                    'tipe' => 'json',
                    'reference_target' => null,
                    'required' => false,
                    'options' => null,
                    'lookup_source' => 'geometry:' . $this->geometryType($dataset),
                    'is_dimension' => false,
                    'urutan' => $existing?->urutan ?? (($dataset->fields()->max('urutan') ?? -1) + 1),
                ]
            );
            $added++;
        }

        $this->command?->info("Field peta opsional ditambahkan pada {$added} dataset yang memiliki konteks lokasi.");
    }

    private function isLocationDataset(Dataset $dataset): bool
    {
        if (in_array(strtolower((string) $dataset->geometry_type), ['point', 'linestring', 'polygon', 'any'], true)) return true;

        foreach ($dataset->fields as $field) {
            $descriptor = strtolower(implode(' ', [$field->nama, $field->label, $field->reference_target, $field->lookup_source]));
            if ($field->reference_target === 'objek_lapangans' || preg_match(self::LOCATION_TERMS, $descriptor)) return true;
        }

        return preg_match(self::LOCATION_TERMS, strtolower($dataset->nama . ' ' . $dataset->kode)) === 1;
    }

    private function geometryType(Dataset $dataset): string
    {
        $descriptor = strtolower($dataset->nama . ' ' . $dataset->kode . ' ' . $dataset->deskripsi);
        if (preg_match('/jalan|ruas|sungai|jalur|saluran|drainase|pantai|pipa/', $descriptor)) return 'linestring';
        if (preg_match('/kawasan|lahan|zona|area|batas|wilayah|irigasi/', $descriptor)) return 'polygon';

        return 'point';
    }

    private function geometryLabel(Dataset $dataset): string
    {
        return match ($this->geometryType($dataset)) {
            'linestring' => 'Gambar jalur atau ruas pada peta',
            'polygon' => 'Gambar kawasan atau area pada peta',
            default => 'Tandai lokasi pada peta',
        };
    }
}
