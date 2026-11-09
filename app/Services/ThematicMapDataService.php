<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\DatasetField;
use App\Models\DatasetValue;
use App\Models\Wilayah;

/** Provides district-level indicators for the public thematic map. */
class ThematicMapDataService
{
    /** Only district aggregates suitable for an open public map. */
    private const PUBLIC_FIELDS = [
        '3.12' => ['DUKCAPIL' => ['total', 'kk_laki', 'kk_perempuan']],
        '3.13' => ['DUKCAPIL' => ['total', 'penduduk_laki', 'penduduk_perempuan']],
        '3.16' => ['DISDIK' => ['jumlah_sekolah']],
        '3.17' => ['DISDIK' => ['jumlah_guru', 'jumlah_murid']],
        '5.57' => ['PUPR' => ['nilai']],
        '5.58' => ['PUPR' => ['nilai', 'terlayani']],
        '5.59' => ['PUPR' => ['nilai']],
        '6.71' => ['DISPERINDAG' => ['jumlah_pasar', 'jumlah_bangunan']],
    ];

    /** @return array{indicators: array<int, array<string, mixed>>, kecamatans: array<int, array<string, mixed>>, profiles: array<string, array<string, mixed>>} */
    public function forAdminMap(): array
    {
        $districts = Wilayah::query()
            ->where('tipe', Wilayah::TIPE_KECAMATAN)
            ->where('aktif', true)
            ->orderBy('nama')
            ->get(['id', 'kode_bps', 'nama']);

        if ($districts->isEmpty()) return ['indicators' => [], 'kecamatans' => [], 'profiles' => []];

        $datasets = Dataset::query()
            ->where('is_active', true)
            ->whereIn('lisensi', [Dataset::LISENSI_CCBY, Dataset::LISENSI_OPEN])
            ->where(function ($query): void {
                foreach (self::PUBLIC_FIELDS as $code => $owners) {
                    foreach ($owners as $owner => $fields) {
                        $query->orWhere(fn ($pair) => $pair->where('kode', $code)->whereHas('opd', fn ($opd) => $opd->where('kode', $owner)));
                    }
                }
            })
            ->whereHas('values', fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('published_at')
                ->where('wilayah_type', Wilayah::TIPE_KECAMATAN)
                ->whereNotNull('wilayah_id')
                ->whereNull('deleted_at')
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%')))
            ->with(['opd', 'fields', 'values' => fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('published_at')
                ->where('wilayah_type', Wilayah::TIPE_KECAMATAN)
                ->whereNotNull('wilayah_id')
                ->whereNull('deleted_at')
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))])
            ->get();

        $indicators = [];
        $profiles = [];
        foreach ($datasets as $dataset) {
            $allowedFields = self::PUBLIC_FIELDS[$dataset->kode][$dataset->opd?->kode] ?? [];
            $numericFields = $dataset->fields->filter(fn (DatasetField $field) =>
                in_array($field->nama, $allowedFields, true)
                && in_array($field->tipe, ['integer', 'decimal'], true)
            );
            if ($numericFields->isEmpty()) continue;

            $valuesByDistrict = $dataset->values->groupBy('wilayah_id');
            foreach ($numericFields as $field) {
                $totals = [];
                $periods = [];
                $latestYear = (int) $dataset->values->max('tahun');
                $descriptor = strtolower(($field->label ?? '') . ' ' . $field->nama . ' ' . ($field->satuan ?? ''));
                $aggregation = preg_match('/%|persen|persentase|rasio|indeks|rata.?rata|rate/', $descriptor) ? 'average' : 'sum';
                foreach ($valuesByDistrict as $districtId => $records) {
                    $yearRecords = $records->where('tahun', $latestYear);
                    $numbers = $yearRecords->map(fn (DatasetValue $record) => data_get($record->values, $field->nama))
                        ->filter(fn ($value) => is_numeric($value))->map(fn ($value) => (float) $value);
                    if ($numbers->isEmpty()) continue;
                    $totals[(string) $districtId] = $aggregation === 'average' ? $numbers->avg() : $numbers->sum();
                    $periods[(string) $districtId] = $yearRecords->pluck('periode_label')->filter()->unique()->implode(', ');
                }
                if ($totals === []) continue;

                $indicators[] = [
                    'id' => $dataset->id . ':' . $field->nama,
                    'dataset' => $dataset->nama,
                    'opd' => $dataset->opd?->nama ?? 'OPD',
                    'field' => $field->nama,
                    'label' => $field->label ?: $field->nama,
                    'unit' => $field->satuan,
                    'year' => $latestYear,
                    'aggregation' => $aggregation,
                    'values' => $totals,
                    'periods' => $periods,
                    'count' => count($totals),
                ];

                foreach ($totals as $districtId => $total) {
                    $profiles[$districtId]['indicators'][] = [
                        'dataset' => $dataset->nama,
                        'dataset_code' => $dataset->kode,
                        'opd' => $dataset->opd?->nama ?? 'OPD',
                        'label' => $field->label ?: $field->nama,
                        'value' => $total,
                        'unit' => $field->satuan,
                        'year' => $latestYear,
                        'period' => $periods[$districtId] ?? null,
                    ];
                }
            }

        }

        usort($indicators, fn (array $a, array $b) => [$a['opd'], $a['dataset'], $a['label']] <=> [$b['opd'], $b['dataset'], $b['label']]);

        return [
            'indicators' => $indicators,
            'kecamatans' => $districts->map(fn (Wilayah $district) => [
                'id' => $district->id,
                'code' => $district->kode_bps,
                'name' => $district->nama,
            ])->all(),
            'profiles' => $profiles,
        ];
    }
}
