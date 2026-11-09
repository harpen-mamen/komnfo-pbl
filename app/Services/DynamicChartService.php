<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\DatasetField;
use App\Models\Wilayah;
use App\Models\Opd;
use App\Models\Dataset;
use App\Models\DatasetValue;

/**
 * Menghasilkan data siap-render untuk DynamicChart component.
 * Setiap dataset yang punya records akan dianalisis strukturnya
 * untuk menentukan chart type (bar/line/pie/area) dan axes.
 */
class DynamicChartService
{
    private array $referenceLabels = [];
    /**
     * Untuk sebuah dataset, tentukan chart type berdasarkan nama & struktur data.
     */
    public function chartMetaForDataset(Dataset $dataset): array
    {
        $nama = strtolower($dataset->nama ?? '');
        $kategori = $dataset->kategori;

        // Donut hanya untuk komposisi kecil dengan kategori yang saling lepas.
        if (str_contains($nama, "komposisi") || str_contains($nama, "distribusi")
            || str_contains($nama, "berdasarkan jenis") || str_contains($nama, "berdasarkan status")) {
            return ["type" => "doughnut", "x_axis" => "label", "y_axis" => "value"];
        }
        // Time series → area
        if (str_contains($nama, "perkembangan") || str_contains($nama, "tren")) {
            return ["type" => "area", "x_axis" => "periode", "y_axis" => "value"];
        }
        // Wilayah dan kategori berurutan lebih mudah dibandingkan dengan batang.
        if (str_contains($nama, "per kecamatan") || str_contains($nama, "per desa") || str_contains($nama, "berdasarkan pendidikan")) {
            return ["type" => "bar", "x_axis" => "kecamatan", "y_axis" => "value"];
        }
        // Bulanan → line
        if (str_contains($nama, "bulanan") || in_array($dataset->period_type, ["bulanan", "tahunan", "triwulanan", "semesteran"], true)) {
            return ["type" => "line", "x_axis" => "periode", "y_axis" => "value"];
        }
        // Default by kategori
        if (in_array($kategori, ["kependudukan", "kesehatan", "pendidikan"])) {
            return ["type" => "bar", "x_axis" => "periode", "y_axis" => "value"];
        }
        return ["type" => "bar", "x_axis" => "periode", "y_axis" => "value"];
    }

    /** Build grouped chart series from published values and dataset field metadata only. */
    public function statisticsForDataset(Dataset $dataset, ?Collection $records = null): array
    {
        $records ??= $this->approvedValuesForDataset($dataset->id)->orderBy('tahun')->orderBy('id')->get();
        if ((string) $dataset->kode === '5.59') {
            return app(InfographicPresentationService::class)->roadCharts($dataset, $records);
        }
        $dimensions = data_get($dataset->config, 'chart_dimensions', []);
        $hasCategory = $dataset->fields->contains(fn ($field) => ($field->is_dimension || in_array($field->nama, $dimensions, true))
            && preg_match('/bulan|tahun|periode|triwulan|semester/i', $field->nama.' '.$field->label) !== 1);
        if ($hasCategory && $records->pluck('tahun')->unique()->count() > 1
            && preg_match('/tren|perkembangan/i', $dataset->nama) !== 1) {
            return $records->groupBy('tahun')->flatMap(fn ($rows) => $this->statisticsForDataset($dataset, $rows->values()))->values()->all();
        }
        return app(InfographicPresentationService::class)->describeCharts($dataset, $records, $this->buildStatisticsForDataset($dataset, $records));
    }

    private function buildStatisticsForDataset(Dataset $dataset, ?Collection $records = null): array
    {
        $records ??= $this->approvedValuesForDataset($dataset->id)->orderBy('tahun')->orderBy('id')->get();
        $config = $dataset->config ?? [];
        $unitField = array_key_exists('chart_unit_field', $config) ? $config['chart_unit_field'] : ($dataset->fields->contains('nama', 'satuan') ? 'satuan' : null);
        if ($unitField && $records->isNotEmpty()) {
            return $records->groupBy(fn ($record) => (string) data_get($record->values, $unitField, 'Tanpa satuan'))
                ->flatMap(function ($rows, $unit) use ($dataset, $config) {
                    $copy = clone $dataset;
                    $copy->config = array_merge($config, ['chart_unit_field'=>null, 'chart_forced_unit'=>(string)$unit]);
                    return $this->buildStatisticsForDataset($copy, $rows->values());
                })->values()->all();
        }
        $fields = $dataset->relationLoaded('fields') ? $dataset->fields : $dataset->fields()->get();
        $numericFields = $fields->filter(fn ($field) => in_array($field->tipe, ['integer', 'decimal'], true)
            && ! in_array($field->nama, ['tahun','latitude','longitude'], true));
        $configuredDimensions = $config['chart_dimensions'] ?? [];
        $dimension = $fields->first(fn ($field) => $field->is_dimension && ! in_array($field->tipe, ['integer', 'decimal'], true))
            ?? $fields->first(fn ($field) => in_array($field->tipe, ['enum', 'reference'], true))
            ?? $fields->first(fn ($field) => in_array($field->tipe, ['string', 'text'], true));
        if ($configuredDimensions !== []) $dimension = $fields->firstWhere('nama', $configuredDimensions[0]) ?? $dimension;
        $hasPeriod = in_array($dataset->period_type, ['bulanan', 'triwulanan', 'semesteran', 'tahunan'], true);
        $periodGroupCount = $hasPeriod
            ? $records->map(fn (DatasetValue $record) => trim(($record->periode_label ? $record->periode_label . ' ' : '') . ($record->tahun ?? 'Tanpa tahun')))->unique()->count()
            : 0;
        $datasetName = strtolower($dataset->nama ?? '');
        $dimensionName = strtolower(($dimension?->nama ?? '') . ' ' . ($dimension?->label ?? ''));
        $dimensionIsPeriod = preg_match('/bulan|month|tahun|year|periode|period|triwulan|semester/', $dimensionName) === 1;
        $nameSuggestsTrend = str_contains($datasetName, 'tren') || str_contains($datasetName, 'perkembangan');
        // Dataset tahunan sering berisi beberapa kategori untuk satu tahun.
        // Gunakan tahun sebagai sumbu waktu hanya bila memang ada beberapa periode
        // dan dimensinya bukan kategori seperti kecamatan, umur, atau bahan pangan.
        $groupByPeriod = $hasPeriod && $periodGroupCount > 1
            && (! $dimension || $dimensionIsPeriod || $nameSuggestsTrend);
        $isDistribution = str_contains($datasetName, 'distribusi')
            || str_contains($datasetName, 'komposisi')
            || str_contains($datasetName, 'proporsi')
            || str_contains($datasetName, 'persentase')
            || str_contains($datasetName, 'berdasarkan jenis')
            || str_contains($datasetName, 'berdasarkan status');
        $isTemporalTrend = $groupByPeriod && ($nameSuggestsTrend || $hasPeriod);
        // Choose chart type from data semantics, with separate handling for
        // period series, categorical shares, and numeric distributions.
        $chartType = match (true) {
            $isTemporalTrend => $nameSuggestsTrend ? 'area' : 'line',
            default => 'bar',
        };
        $totalRows = $dimension
            ? $records->filter(fn (DatasetValue $record) => mb_strtolower(trim((string) data_get($record->values, $dimension->nama))) === 'total')
            : collect();
        $chartRecords = $totalRows->isEmpty() ? $records : $records->diff($totalRows)->values();
        $numericFields = $numericFields->filter(fn ($field) => $chartRecords->contains(
            fn (DatasetValue $record) => is_numeric(data_get($record->values, $field->nama))
        ));
        $categoryCount = $chartRecords->map(fn (DatasetValue $record) => $groupByPeriod
            ? trim(($record->periode_label ? $record->periode_label . ' ' : '') . ($record->tahun ?? 'Tanpa tahun'))
            : (string) ($dimension ? data_get($record->values, $dimension->nama) : ($record->periode_label ?: $record->tahun)))
            ->filter()->unique()->count();
        if (! $groupByPeriod && $isDistribution && $categoryCount >= 2 && $categoryCount <= 10 && $numericFields->count() === 1) {
            $chartType = $categoryCount <= 6 ? 'doughnut' : 'pie';
        }
        $isAverageMetric = static function (string $name): bool {
            $name = strtolower($name);
            foreach (['harga', 'price', 'persen', 'persentase', 'rasio', 'rate', 'indeks', 'rata rata', 'rata_rata'] as $term) {
                if (str_contains($name, $term)) return true;
            }
            return false;
        };
        $charts = [];

        foreach ($numericFields as $metric) {
            $metricName = $metric->nama . ' ' . $metric->label;
            $averageMetric = $isAverageMetric($metricName);
            $unit = $config['chart_forced_unit'] ?? ($metric->satuan ?: (preg_match('/\(([^)]+)\)/', $metric->label, $match) ? $match[1] : ''));
            $groups = $chartRecords->filter(fn (DatasetValue $record) => is_numeric(data_get($record->values, $metric->nama)))
                ->groupBy(function (DatasetValue $record) use ($dimension, $groupByPeriod, $configuredDimensions, $fields): string {
                if ($groupByPeriod) {
                    return trim(($record->periode_label && $record->periode_label !== (string) $record->tahun ? $record->periode_label . ' ' : '') . ($record->tahun ?? 'Tanpa tahun'));
                }
                if ($configuredDimensions !== []) {
                    return collect($configuredDimensions)->map(function ($name) use ($record, $fields) {
                        $value = data_get($record->values, $name);
                        $field = $fields->firstWhere('nama', $name);
                        return $field && is_scalar($value) ? $this->dimensionLabel($field, (string)$value) : (string)$value;
                    })->filter()->implode(' / ');
                }
                $label = $dimension ? data_get($record->values, $dimension->nama) : null;
                if (is_scalar($label) && trim((string) $label) !== '') {
                    $label = $this->dimensionLabel($dimension, (string) $label);
                    return $label;
                }
                return trim(($record->periode_label ? $record->periode_label . ' ' : '') . ($record->tahun ?? 'Tanpa periode'));
            });
            $groupValue = fn ($rows) => $averageMetric
                ? round($rows->avg(fn (DatasetValue $row) => is_numeric(data_get($row->values, $metric->nama)) ? (float) data_get($row->values, $metric->nama) : null) ?? 0, 6)
                : round($rows->sum(fn (DatasetValue $row) => is_numeric(data_get($row->values, $metric->nama)) ? (float) data_get($row->values, $metric->nama) : 0), 6);
            $allNumericValues = $chartRecords->map(fn (DatasetValue $row) => data_get($row->values, $metric->nama))->filter(fn ($value) => is_numeric($value));
            $reportedTotal = $totalRows->map(fn (DatasetValue $row) => data_get($row->values, $metric->nama))->first(fn ($value) => is_numeric($value));
            $showAverage = $groupByPeriod || str_contains(strtolower($metricName), 'harga') || str_contains(strtolower($metricName), 'price');
            $summaryValue = is_numeric($reportedTotal)
                ? round((float) $reportedTotal, 2)
                : ($averageMetric
                    ? round($allNumericValues->avg() ?? 0, 2)
                    : ($dimension ? null : round($allNumericValues->sum(), 2)));

            $chartLabels = $groups->keys()->values()->all();
            $chartValues = $groups->map($groupValue)->values()->all();
            $metricChartType = $chartType;
            if ($configuredDimensions === [] && ! $groupByPeriod && $categoryCount >= 12 && $allNumericValues->count() >= 12 && $numericFields->count() === 1) {
                $metricChartType = 'histogram';
            }
            if ($metricChartType === 'histogram') {
                $distribution = $this->histogram($allNumericValues->map(fn ($value) => (float) $value)->all());
                $chartLabels = $distribution['labels'];
                $chartValues = $distribution['values'];
            }

            $charts[] = [
                'metric' => $metric->nama,
                'title' => $metric->label.(!empty($config['chart_forced_unit']) ? ' ('.$unit.')' : ''),
                'type' => $metricChartType,
                'labels' => $chartLabels,
                'values' => $chartValues,
                'summary_label' => is_numeric($reportedTotal) ? 'Angka laporan' : ($averageMetric ? 'Rata-rata' : ($dimension ? null : 'Total')),
                'summary_value' => $summaryValue,
                'average' => $showAverage ? round($allNumericValues->avg() ?? 0, 2) : null,
                'group_label' => $groupByPeriod ? 'periode' : 'kategori',
                'grouping' => $groupByPeriod ? 'time' : 'category',
                'unit' => $unit,
            ];
        }

        if ($charts === [] && $records->isNotEmpty()) {
            $groups = $records->groupBy(fn (DatasetValue $record) => trim(($record->periode_label ? $record->periode_label . ' ' : '') . ($record->tahun ?? 'Tanpa periode')));
            $charts[] = [
                'title' => 'Jumlah catatan terbit',
                'unit' => 'data',
                'type' => 'bar',
                'labels' => $groups->keys()->values()->all(),
                'values' => $groups->map->count()->values()->all(),
                'total' => $records->count(),
                'average' => $groups->isEmpty() ? 0 : round($records->count() / $groups->count(), 2),
            ];
        }

        return $charts;
    }

    /** Build a compact frequency distribution for numeric observations. */
    private function histogram(array $values): array
    {
        if ($values === []) return ['labels' => [], 'values' => []];

        $minimum = min($values);
        $maximum = max($values);
        if ($minimum === $maximum) {
            return ['labels' => [(string) $minimum], 'values' => [count($values)]];
        }

        $binCount = min(10, max(5, (int) ceil(sqrt(count($values)))));
        $width = ($maximum - $minimum) / $binCount;
        $bins = array_fill(0, $binCount, 0);
        foreach ($values as $value) {
            $index = min($binCount - 1, (int) floor(($value - $minimum) / $width));
            $bins[$index]++;
        }

        $precision = max(0, min(3, (int) ceil(-log10($width))));
        $labels = [];
        for ($index = 0; $index < $binCount; $index++) {
            $start = $minimum + ($width * $index);
            $end = $index === $binCount - 1 ? $maximum : $start + $width;
            $labels[] = number_format($start, $precision, ',', '.') . '–' . number_format($end, $precision, ',', '.');
        }

        return ['labels' => $labels, 'values' => $bins];
    }

    /** Convert stored enum keys such as "Minyak/Lemak" to their display label. */
    private function dimensionLabel(?DatasetField $dimension, string $value): string
    {
        $target = $dimension?->reference_target;
        if ($dimension?->tipe === 'reference' && in_array($target, ['wilayah','wilayahs','opd','opds'], true)) {
            $model = in_array($target, ['wilayah','wilayahs'], true) ? Wilayah::class : Opd::class;
            $this->referenceLabels[$model] ??= $model::pluck('nama', 'id')->all();
            return (string)($this->referenceLabels[$model][$value] ?? 'Referensi belum ditemukan (ID '.$value.')');
        }
        $options = $dimension?->options;
        if (! is_array($options) || $options === []) return $value;

        if (array_is_list($options)) {
            return (string) (collect($options)->first(fn ($option) => (string) $option === $value) ?? $value);
        }

        if (array_key_exists($value, $options)) return (string) $options[$value];

        $key = array_search($value, $options, true);
        return $key === false ? $value : (string) $options[$key];
    }

    /**
     * Build chart data for a dataset.
     * @param array $filters year, periode_type, dataset_id
     */
    public function chartDataForDataset(int $datasetId, array $filters = []): array
    {
        $ds = Dataset::with('fields')->find($datasetId);
        if (!$ds) {
            return ["labels" => [], "data" => [], "type" => "bar"];
        }
        $meta = $this->chartMetaForDataset($ds);

        $records = $this->approvedValuesForDataset($datasetId)
            ->when(!empty($filters['year']), fn ($query) => $query->where('tahun', $filters['year']))
            ->orderBy('tahun')
            ->orderBy('id')
            ->get();
        $metric = $ds->fields->first(fn ($field) => in_array($field->tipe, ['integer', 'decimal'], true));

        $labels = [];
        $data = [];
        foreach ($records as $r) {
            $value = $metric ? data_get($r->values, $metric->nama) : null;
            if (is_numeric($value)) {
                $labels[] = $r->periode_label ?: (string) ($r->tahun ?? $r->id);
                $data[] = (float) $value;
            }
        }

        return ["labels" => $labels, "data" => $data, "type" => $meta["type"]];
    }

    /**
     * List all datasets of an OPD, each with chart type detected.
     */
    public function datasetsForOpd(int $opdId): array
    {
        $datasets = Dataset::where("opd_id", $opdId)
            ->where("is_active", true)
            ->orderBy("kategori")
            ->orderBy("nama")
            ->get();
        return $datasets->map(function (Dataset $d) {
            $meta = $this->chartMetaForDataset($d);
            $recordsCount = $this->approvedValuesForDataset($d->id)->count();
            return [
                "id" => $d->id,
                "kode" => $d->kode,
                "nama" => $d->nama,
                "kategori" => $d->kategori,
                "satuan" => data_get($d->config, 'satuan'),
                "chart_type" => $meta["type"],
                "x_axis" => $meta["x_axis"],
                "y_axis" => $meta["y_axis"],
                "records_count" => $recordsCount,
            ];
        })->all();
    }

    /**
     * Get distinct available years from records of an OPD.
     */
    public function availableYears(int $opdId): array
    {
        $opdDatasetIds = Dataset::where("opd_id", $opdId)->pluck("id");
        return $this->approvedValuesForDatasetIds($opdDatasetIds)
            ->whereNotNull("tahun")
            ->distinct()
            ->orderByDesc("tahun")
            ->pluck("tahun")
            ->all();
    }

    /**
     * KPI dinamis untuk OPD — based on numeric datasets with latest record.
     */
    public function dynamicKpis(int $opdId, int $maxKpis = 8): array
    {
        $datasets = Dataset::with('fields')->where("opd_id", $opdId)->where("is_active", true)->get();
        $kpis = [];
        foreach ($datasets as $ds) {
            $latest = $this->approvedValuesForDataset($ds->id)->latest('id')->first();
            $metric = $ds->fields->first(fn ($field) => in_array($field->tipe, ['integer', 'decimal'], true));
            $value = $metric && $latest ? data_get($latest->values, $metric->nama) : null;
            if (!is_numeric($value)) {
                continue;
            }
            $kpis[] = [
                "id" => $ds->id,
                "label" => $ds->nama,
                "value" => (float) $value,
                "satuan" => data_get($ds->config, 'satuan'),
                "kategori" => $ds->kategori ?? '',
                "color" => $this->colorForKategori($ds->kategori ?? ''),
                "icon" => $this->iconForKategori($ds->kategori ?? ''),
                "dataset_id" => $ds->id,
                "last_period" => $latest?->periode_label ?: (string) ($latest?->tahun ?? ''),
            ];
            if (count($kpis) >= $maxKpis) {
                break;
            }
        }
        return $kpis;
    }

    private function approvedValuesForDataset(int $datasetId)
    {
        return $this->approvedValuesForDatasetIds([$datasetId]);
    }

    private function approvedValuesForDatasetIds($datasetIds)
    {
        return DatasetValue::query()
            ->whereIn('dataset_id', $datasetIds)
            ->where('status', DatasetValue::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    private function colorForKategori(string $kategori): string
    {
        return match ($kategori) {
            "kependudukan" => "blue",
            "kesehatan" => "rose",
            "pendidikan" => "emerald",
            "perhubungan" => "sky",
            "kebencanaan" => "red",
            "lingkungan_hidup" => "emerald",
            "pertanian" => "emerald",
            "perikanan" => "sky",
            "perumahan" => "amber",
            "sosial" => "rose",
            "keuangan" => "emerald",
            "pemerintahan" => "indigo",
            "pariwisata" => "amber",
            default => "slate",
        };
    }

    private function iconForKategori(string $kategori): string
    {
        return match ($kategori) {
            "kependudukan" => "users",
            "kesehatan" => "heart-pulse",
            "pendidikan" => "academic-cap",
            "perhubungan" => "truck",
            "kebencanaan" => "exclamation-triangle",
            "lingkungan_hidup" => "leaf",
            "pertanian" => "globe-alt",
            "perikanan" => "fish",
            "perumahan" => "home",
            "sosial" => "heart",
            "keuangan" => "currency-dollar",
            "pemerintahan" => "building-library",
            "pariwisata" => "sun",
            default => "chart-bar",
        };
    }
}
