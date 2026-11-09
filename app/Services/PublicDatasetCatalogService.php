<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\DatasetValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Curated, public-safe view of verified datasets. This is an explicit allowlist:
 * publication alone never makes a raw dataset available to the public portal.
 */
class PublicDatasetCatalogService
{
    /** @var array<string, string> Dataset code => authoritative owner OPD code. */
    private const ALLOWED_DATASETS = [
        '2.1' => 'SETDA',
        '3.12' => 'DUKCAPIL', '3.13' => 'DUKCAPIL',
        '3.16' => 'DISDIK', '3.17' => 'DISDIK',
        '4.41' => 'DISTAN', '4.42' => 'DISTAN', '4.43' => 'DISTAN',
        '4.46' => 'PERIKANAN', '4.47' => 'PERIKANAN',
        '5.57' => 'PUPR', '5.58' => 'PUPR', '5.59' => 'PUPR', '5.61' => 'PERKIM',
        '6.68' => 'DISPERINDAG', '6.71' => 'DISPERINDAG',
        '7.73' => 'BPKPD',
        'IKU_DAERAH' => 'BAPPELITBANG', 'IRBI_2025' => 'BPBD',
        'PRODUK_DPRD' => 'SETWAN', 'PULAU_KECIL_TERLUAR' => 'BATAS',
    ];

    public function all(): array
    {
        return Cache::remember('public.dataset.catalog.v1', now()->addMinute(), function (): array {
            return $this->datasets()->map(fn (Dataset $dataset): array => $this->present($dataset))->values()->all();
        });
    }

    public function find(string $slug): ?array
    {
        return collect($this->all())->firstWhere('slug', $slug);
    }

    public function categories(): array
    {
        return collect($this->all())->groupBy('category')
            ->map(fn ($items, $name): array => ['id' => Str::slug((string) $name), 'name' => $name, 'count' => $items->count()])
            ->values()->all();
    }

    /** @return \Illuminate\Support\Collection<int, Dataset> */
    private function datasets()
    {
        return Dataset::query()
            ->where('is_active', true)
            ->whereIn('lisensi', [Dataset::LISENSI_CCBY, Dataset::LISENSI_OPEN])
            ->where(function ($query): void {
                foreach (self::ALLOWED_DATASETS as $code => $opdCode) {
                    $query->orWhere(function ($pair) use ($code, $opdCode): void {
                        $pair->where('kode', $code)->whereHas('opd', fn ($opd) => $opd->where('kode', $opdCode));
                    });
                }
            })
            ->whereHas('values', fn ($values) => $this->publicValues($values))
            ->with(['opd', 'fields', 'values' => fn ($values) => $this->publicValues($values)->orderBy('tahun')->orderBy('id')])
            ->orderBy('kategori')->orderBy('nama')->get();
    }

    private function publicValues($query)
    {
        return $query->where('status', DatasetValue::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->whereIn('wilayah_type', ['kabupaten', 'kecamatan'])
            ->whereNull('deleted_at')
            ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'));
    }

    private function present(Dataset $dataset): array
    {
        $allRecords = $dataset->values;
        $latestYear = $allRecords->max('tahun');
        $records = $latestYear === null ? $allRecords : $allRecords->where('tahun', $latestYear)->values();
        $charts = collect(app(DynamicChartService::class)->statisticsForDataset($dataset, $records))
            ->map(fn (array $chart): array => [
                'title' => (string) ($chart['title'] ?? $dataset->nama),
                'unit' => (string) ($chart['unit'] ?? ''),
                'labels' => array_map(fn ($label): string => (string) $label, $chart['labels'] ?? []),
                'values' => array_values(array_map(fn ($value): float => (float) $value, $chart['values'] ?? [])),
                'description' => (string) ($chart['description'] ?? ''),
                'aggregation_note' => (string) ($chart['aggregation_note'] ?? ''),
            ])->values()->all();

        $context = app(InfographicPresentationService::class)->context($dataset, $records);
        $sourceUrl = data_get($dataset->config, 'source_url') ?: $this->sourceUrl($records);
        $charts = collect(app(InfographicPresentationService::class)->describeCharts($dataset, $records, $charts))->map(fn (array $chart): array => [
            'title' => $chart['title'] ?? $dataset->nama,
            'unit' => $chart['unit'] ?? '',
            'labels' => array_values($chart['labels'] ?? []),
            'values' => array_values(array_map(fn ($value) => (float) $value, $chart['values'] ?? [])),
            'description' => $chart['description'] ?? '',
            'aggregation_note' => $chart['aggregation_note'] ?? '',
        ])->values()->all();
        $category = match (true) {
            in_array($dataset->kode, ['2.1', 'PRODUK_DPRD'], true) => 'Pemerintahan',
            in_array($dataset->kode, ['3.12', '3.13'], true) => 'Kependudukan',
            in_array($dataset->kode, ['3.16', '3.17'], true) => 'Pendidikan',
            in_array($dataset->kode, ['4.41', '4.42', '4.43', '4.46', '4.47', '6.68', '6.71'], true) => 'Ekonomi',
            in_array($dataset->kode, ['5.57', '5.58', '5.59', '5.61'], true) => 'Infrastruktur',
            in_array($dataset->kode, ['7.73', 'IKU_DAERAH'], true) => 'Kinerja Daerah',
            in_array($dataset->kode, ['IRBI_2025', 'PULAU_KECIL_TERLUAR'], true) => 'Wilayah dan Kebencanaan',
            default => 'Data Daerah',
        };

        return [
            'id' => $dataset->id,
            'slug' => Str::slug(($dataset->opd?->kode ?? 'opd').'-'.$dataset->kode),
            'code' => $dataset->kode_sdi ?: $dataset->kode,
            'title' => $dataset->nama,
            'category' => $category,
            'description' => Str::limit(strip_tags((string) $dataset->deskripsi), 280),
            'opd' => $dataset->opd?->nama,
            'published_at' => $records->max('published_at')?->toIso8601String(),
            'years' => $records->pluck('tahun')->filter()->unique()->sort()->values()->all(),
            'source' => data_get($dataset->config, 'source_publication') ?: (str_starts_with((string) $dataset->kode, '5.') && collect($records)->contains(fn ($record) => str_contains((string) $record->notes, 'Statistik Sektoral')) ? 'Statistik Sektoral Kabupaten Kepulauan Sangihe 2024' : 'Data resmi terbit dari ' . ($dataset->opd?->nama ?? 'OPD')),
            'source_url' => $sourceUrl,
            'charts' => $charts,
        ];
    }

    private function sourceUrl($records): ?string
    {
        foreach ($records as $record) {
            $notes = (string) $record->notes;
            if (! preg_match('~^\[PUBLIC-SOURCE:[^\]]+\]\s*(https://[^\s\]]+)~u', $notes, $match)) continue;
            $url = rtrim($match[1], '.,;)');
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            if ($host !== '' && str_ends_with($host, '.go.id')) return $url;
        }
        return null;
    }
}
