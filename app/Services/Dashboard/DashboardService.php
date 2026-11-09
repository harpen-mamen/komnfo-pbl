<?php

namespace App\Services\Dashboard;

use Throwable;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * DashboardService - Mengumpulkan statistik untuk dashboard eksekutif dan per-OPD.
 *
 * Methods:
 * - getOverviewStats(): ringkasan total
 * - getStatsByOpd(): dataset per OPD
 * - getStatsByStatus(): breakdown status verifikasi
 * - getStatsByKategori(): per kategori
 * - getRecentActivity(): activity log
 * - getTopDatasetsByData(): dataset terpadat
 * - getPeriodCoverage(): distribusi per periode
 */
class DashboardService
{
    /** Published, aggregate-only data for the Bupati monitoring page. */
    public function getLeadershipMonitoring(): array
    {
        $datasets = Dataset::query()
            ->where('is_active', true)
            ->with('opd:id,kode,nama')
            ->withCount(['values as published_values_count' => fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('published_at')
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))])
            ->get();

        $published = fn (Dataset $dataset): int => (int) $dataset->published_values_count;
        $datasetCard = fn (Dataset $dataset): array => [
            'code' => $dataset->kode,
            'name' => $dataset->nama,
            'opd' => $dataset->opd?->nama ?? 'Perangkat daerah',
            'published' => $published($dataset),
        ];
        $findDatasets = function (array $opdCodes, string $pattern) use ($datasets): \Illuminate\Support\Collection {
            return $datasets->filter(fn (Dataset $dataset): bool =>
                in_array(strtoupper((string) $dataset->opd?->kode), $opdCodes, true)
                && preg_match($pattern, $dataset->kode.' '.$dataset->nama) === 1
            )->values();
        };
        $valueRows = function ($datasetSet): array {
            $ids = $datasetSet->pluck('id')->all();
            if ($ids === []) return [];

            return DB::table('dataset_values as v')
                ->join('datasets as d', 'd.id', '=', 'v.dataset_id')
                ->join('opds as o', 'o.id', '=', 'd.opd_id')
                ->whereIn('d.id', $ids)
                ->where('v.status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('v.published_at')
                ->whereNull('v.deleted_at')
                ->where(fn ($notes) => $notes->whereNull('v.notes')->orWhere('v.notes', 'not like', '[DEMO-%'))
                ->select('v.id', 'v.dataset_id', 'v.tahun', 'v.periode_label', 'v.wilayah_type', 'v.wilayah_id', 'v.values as payload', 'v.published_at', 'd.kode as dataset_code', 'd.nama as dataset_name', 'o.nama as opd_name')
                ->orderByDesc('v.tahun')
                ->get()
                ->map(function ($row): array {
                    $row = (array) $row;
                    $row['values'] = json_decode((string) $row['payload'], true) ?: [];
                    unset($row['payload']);
                    return $row;
                })
                ->all();
        };
        $numeric = static function ($value): ?float {
            if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) return (float) $value;
            if (! is_string($value)) return null;
            $clean = preg_replace('/[^0-9,.-]/u', '', $value);
            if ($clean === '' || ! preg_match('/\d/', $clean)) return null;
            if (str_contains($clean, ',') && str_contains($clean, '.')) {
                $clean = strrpos($clean, ',') > strrpos($clean, '.')
                    ? str_replace(',', '.', str_replace('.', '', $clean))
                    : str_replace(',', '', $clean);
            } elseif (str_contains($clean, ',')) {
                $clean = preg_match('/,\d{1,2}$/', $clean) ? str_replace(',', '.', $clean) : str_replace(',', '', $clean);
            }
            return is_numeric($clean) ? (float) $clean : null;
        };
        $indicatorSets = $findDatasets(['BAPPELITBANG', 'BAPPEDA'], '/rpjmd|iku|indikator kinerja|program prioritas/i');
        $indicatorRows = $valueRows($indicatorSets);
        $indicators = [];
        foreach ($indicatorRows as $row) {
            $values = $row['values'];
            $target = $numeric($values['target'] ?? $values['target_tahunan'] ?? null);
            $actual = $numeric($values['realisasi'] ?? $values['capaian'] ?? null);
            $indicators[] = [
                'name' => $values['indikator'] ?? $values['nama_indikator'] ?? $values['sasaran'] ?? $row['dataset_name'],
                'objective' => $values['tujuan'] ?? $values['sasaran'] ?? 'Indikator daerah',
                'year' => $values['tahun'] ?? $row['tahun'],
                'target' => $target,
                'actual' => $actual,
                'unit' => $values['satuan'] ?? '',
                'progress' => $target && $actual !== null ? round(($actual / $target) * 100, 1) : null,
            ];
        }
        $indicatorTrend = collect($indicators)->filter(fn (array $row): bool => $row['progress'] !== null)
            ->groupBy('year')
            ->map(fn ($rows, $year): array => ['year' => (string) $year, 'progress' => round($rows->avg('progress'), 1)])
            ->sortKeys()->values()->all();

        // Budget figures are only read from published BPKPD records. This page has no write path.
        $budgetSets = $findDatasets(['BPKPD'], '/apbd|anggaran|pagu|realisasi/i');
        $budgetRows = $valueRows($budgetSets);
        $budgetTotal = function (array $keys) use ($budgetRows, $numeric): ?float {
            $values = [];
            foreach ($budgetRows as $row) foreach ($keys as $key) if (array_key_exists($key, $row['values'])) {
                $parsed = $numeric($row['values'][$key]);
                if ($parsed !== null) $values[] = $parsed;
                break;
            }
            return $values === [] ? null : array_sum($values);
        };
        $budgetPagu = $budgetTotal(['pagu_anggaran', 'pagu']);
        $budgetRealization = $budgetTotal(['realisasi_anggaran', 'realisasi']);
        $physicalValues = [];
        foreach ($budgetRows as $row) {
            foreach (['realisasi_fisik', 'fisik_realisasi'] as $key) {
                if (array_key_exists($key, $row['values'])) {
                    $parsed = $numeric($row['values'][$key]);
                    if ($parsed !== null) $physicalValues[] = $parsed;
                    break;
                }
            }
        }
        $budgetPhysical = $physicalValues === [] ? null : array_sum($physicalValues) / count($physicalValues);
        $budgetTarget = $budgetTotal(['target_fisik']);
        $budgetProgress = $budgetPagu && $budgetRealization !== null ? round(($budgetRealization / $budgetPagu) * 100, 1) : null;
        $budgetItems = array_map(function (array $row) use ($numeric): array {
            $v = $row['values'];
            $pagu = $numeric($v['pagu_anggaran'] ?? $v['pagu'] ?? null);
            $realization = $numeric($v['realisasi_anggaran'] ?? $v['realisasi'] ?? null);
            return [
                'opd' => $v['perangkat_daerah'] ?? $row['opd_name'],
                'program' => $v['program'] ?? $v['kegiatan'] ?? $v['item'] ?? $row['dataset_name'],
                'year' => $v['tahun'] ?? $row['tahun'],
                'pagu' => $pagu,
                'realization' => $realization,
                'progress' => $pagu && $realization !== null ? round(($realization / $pagu) * 100, 1) : null,
                'physical' => $numeric($v['realisasi_fisik'] ?? $v['fisik_realisasi'] ?? null),
            ];
        }, $budgetRows);
        $budgetTrend = collect($budgetItems)->filter(fn (array $row): bool => $row['progress'] !== null)
            ->groupBy('year')
            ->map(fn ($rows, $year): array => ['year' => (string) $year, 'progress' => round($rows->avg('progress'), 1)])
            ->sortKeys()->values()->all();

        $sectorDefinitions = [
            'pendidikan' => ['label' => 'Pendidikan', 'codes' => ['DISDIK']],
            'kesehatan' => ['label' => 'Kesehatan', 'codes' => ['DINKES']],
            'infrastruktur' => ['label' => 'Infrastruktur', 'codes' => ['PUPR', 'DISHUB', 'PERKIM', 'DISKOMINFO']],
            'ekonomi' => ['label' => 'Ekonomi', 'codes' => ['DISTAN', 'PERIKANAN', 'DISPERINDAG', 'KOPERASI', 'DPMPTSP', 'BPKPD']],
        ];
        $sectors = [];
        foreach ($sectorDefinitions as $key => $definition) {
            $set = $datasets->filter(fn (Dataset $dataset): bool => in_array(strtoupper((string) $dataset->opd?->kode), $definition['codes'], true));
            $sectors[$key] = [
                'label' => $definition['label'],
                'dataset_count' => $set->count(),
                'published_values' => (int) $set->sum('published_values_count'),
                'datasets' => $set->sortByDesc('published_values_count')->take(3)->map($datasetCard)->all(),
            ];
        }

        $infraPatterns = [
            'BTS dan menara' => '/bts|menara|telekomunikasi/i',
            'Blankspot' => '/blank.?spot|wilayah.?tanpa.?sinyal/i',
            'Penyedia internet' => '/penyedia.*internet|isp|layanan internet/i',
            'Internet BAKTI dan Starlink' => '/bakti|starlink/i',
            'Fiber optik' => '/fiber.?optik|serat.?optik/i',
            'Gangguan jaringan' => '/gangguan.*(jaringan|internet)|pemulihan jaringan/i',
        ];
        $infrastructureInventory = [];
        foreach ($infraPatterns as $label => $pattern) {
            $set = $datasets->filter(fn (Dataset $dataset): bool => preg_match($pattern, $dataset->kode.' '.$dataset->nama) === 1);
            $infrastructureInventory[] = [
                'label' => $label,
                'dataset_count' => $set->count(),
                'published_values' => (int) $set->sum('published_values_count'),
            ];
        }
        $mapSets = $datasets->filter(fn (Dataset $dataset): bool => in_array($dataset->kode, ['LOKASI_FASKES_PUBLIK', 'LOKASI_TRANSPORTASI_PUBLIK'], true));
        $mapRows = $valueRows($mapSets);
        $mapPoints = [];
        foreach ($mapRows as $row) {
            $v = $row['values'];
            $lat = $numeric($v['latitude'] ?? null);
            $lng = $numeric($v['longitude'] ?? null);
            if ($lat === null || $lng === null || $lat < 2.5 || $lat > 5.5 || $lng < 124 || $lng > 127.5) continue;
            if (str_starts_with((string) ($v['status_lokasi'] ?? ''), 'Koordinat sumber ditolak')) continue;
            $mapPoints[] = [
                'name' => $v['nama'] ?? 'Titik layanan publik',
                'type' => $v['jenis'] ?? 'Layanan publik',
                'lat' => $lat,
                'lng' => $lng,
                'source' => $v['sumber_url'] ?? null,
                'dataset' => $row['dataset_name'],
            ];
        }

        $districts = DB::table('wilayahs as w')
            ->leftJoin('wilayahs as village', function ($join): void {
                $join->on('village.parent_id', '=', 'w.id')->where('village.tipe', '=', Wilayah::TIPE_DESA)->whereNull('village.deleted_at');
            })
            ->leftJoin('dataset_values as v', function ($join): void {
                $join->on('v.wilayah_id', '=', 'w.id')->where('v.wilayah_type', '=', Wilayah::TIPE_KECAMATAN)
                    ->where('v.status', '=', DatasetValue::STATUS_PUBLISHED)->whereNotNull('v.published_at')->whereNull('v.deleted_at')
                    ->where(fn ($notes) => $notes->whereNull('v.notes')->orWhere('v.notes', 'not like', '[DEMO-%'));
            })
            ->where('w.tipe', Wilayah::TIPE_KECAMATAN)
            ->whereNull('w.deleted_at')
            ->groupBy('w.id', 'w.nama')
            ->orderBy('w.nama')
            ->select('w.id', 'w.nama', DB::raw('COUNT(DISTINCT village.id) as village_count'), DB::raw('COUNT(DISTINCT v.id) as published_values'))
            ->get()
            ->map(fn ($row): array => (array) $row)
            ->all();
        $territoryCodes = ['DISDIK', 'DINKES', 'PUPR', 'DISHUB', 'DPMD', 'DUKCAPIL', 'DISTAN', 'PERIKANAN', 'DISPERINDAG', 'KOPERASI'];
        $territoryRows = DB::table('dataset_values as v')
            ->join('datasets as d', 'd.id', '=', 'v.dataset_id')
            ->join('opds as o', 'o.id', '=', 'd.opd_id')
            ->where('v.status', DatasetValue::STATUS_PUBLISHED)->whereNotNull('v.published_at')->whereNull('v.deleted_at')
            ->where(fn ($notes) => $notes->whereNull('v.notes')->orWhere('v.notes', 'not like', '[DEMO-%'))
            ->where('v.wilayah_type', Wilayah::TIPE_KECAMATAN)
            ->whereIn('o.kode', $territoryCodes)
            ->groupBy('v.wilayah_id', 'o.kode')
            ->select('v.wilayah_id', 'o.kode', DB::raw('COUNT(v.id) as records'))
            ->get()
            ->groupBy('wilayah_id');
        $sectorCodesByKey = [
            'education_values' => ['DISDIK'],
            'health_values' => ['DINKES'],
            'infrastructure_values' => ['PUPR', 'DISHUB'],
            'community_development_values' => ['DPMD', 'DUKCAPIL'],
            'economic_values' => ['DISTAN', 'PERIKANAN', 'DISPERINDAG', 'KOPERASI'],
        ];
        $districts = array_map(function (array $district) use ($territoryRows, $sectorCodesByKey): array {
            $rows = $territoryRows->get($district['id'], collect());
            foreach ($sectorCodesByKey as $key => $codes) {
                $district[$key] = (int) $rows->whereIn('kode', $codes)->sum('records');
            }
            return $district;
        }, $districts);

        $reportRows = DB::table('opds as o')
            ->leftJoin('datasets as d', function ($join): void { $join->on('d.opd_id', '=', 'o.id')->whereNull('d.deleted_at'); })
            ->leftJoin('dataset_values as v', function ($join): void {
                $join->on('v.dataset_id', '=', 'd.id')->where('v.status', '=', DatasetValue::STATUS_PUBLISHED)->whereNotNull('v.published_at')->whereNull('v.deleted_at');
                $join->where(fn ($notes) => $notes->whereNull('v.notes')->orWhere('v.notes', 'not like', '[DEMO-%'));
            })
            ->where('o.aktif', true)->groupBy('o.id', 'o.kode', 'o.nama')->orderBy('o.nama')
            ->select('o.kode', 'o.nama', DB::raw('COUNT(DISTINCT d.id) as dataset_count'), DB::raw('COUNT(v.id) as published_values'))
            ->get()->map(fn ($row): array => (array) $row)->all();
        $cadence = $datasets->groupBy(fn (Dataset $dataset): string => (string) ($dataset->period_type ?: 'belum diatur'))
            ->map(fn ($set, $period): array => ['period' => $period, 'datasets' => $set->count(), 'published_values' => (int) $set->sum('published_values_count')])
            ->sortBy('period')->values()->all();
        $history = DB::table('dataset_values as v')
            ->where('v.status', DatasetValue::STATUS_PUBLISHED)->whereNotNull('v.published_at')->whereNull('v.deleted_at')
            ->where(fn ($notes) => $notes->whereNull('v.notes')->orWhere('v.notes', 'not like', '[DEMO-%'))
            ->groupBy(DB::raw("DATE_FORMAT(v.published_at, '%Y-%m')"))
            ->orderByDesc(DB::raw("DATE_FORMAT(v.published_at, '%Y-%m')"))
            ->limit(12)
            ->select(DB::raw("DATE_FORMAT(v.published_at, '%Y-%m') as month"), DB::raw('COUNT(*) as values_count'), DB::raw('COUNT(DISTINCT v.dataset_id) as datasets_count'))
            ->get()->map(fn ($row): array => (array) $row)->all();
        $headlineRows = $valueRows($datasets->whereIn('kode', ['3.12', '3.13', '3.21'])->values());
        $populationTotal = 0.0;
        $householdTotal = 0.0;
        $healthIndicators = [];
        $populationByDistrict = [];
        $householdsByDistrict = [];
        foreach ($headlineRows as $row) {
            $values = $row['values'];
            if ($row['dataset_code'] === '3.13') $populationTotal += $numeric($values['total'] ?? 0) ?? 0;
            if ($row['dataset_code'] === '3.12') $householdTotal += $numeric($values['total'] ?? 0) ?? 0;
            if ($row['dataset_code'] === '3.13' && $row['wilayah_id']) $populationByDistrict[$row['wilayah_id']] = $numeric($values['total'] ?? null);
            if ($row['dataset_code'] === '3.12' && $row['wilayah_id']) $householdsByDistrict[$row['wilayah_id']] = $numeric($values['total'] ?? null);
            if ($row['dataset_code'] === '3.21' && isset($values['indikator'])) {
                $healthIndicators[$values['indikator']] = [
                    'value' => $numeric($values['nilai'] ?? null),
                    'unit' => $values['satuan'] ?? '',
                    'year' => $values['tahun'] ?? $row['tahun'],
                ];
            }
        }
        $districts = array_map(function (array $district) use ($populationByDistrict, $householdsByDistrict): array {
            $district['population'] = $populationByDistrict[$district['id']] ?? null;
            $district['households'] = $householdsByDistrict[$district['id']] ?? null;
            return $district;
        }, $districts);

        return [
            'summary' => [
                'opd_count' => Opd::query()->where('aktif', true)->count(),
                'dataset_count' => $datasets->count(),
                'published_values' => (int) $datasets->sum('published_values_count'),
                'district_count' => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->count(),
                'village_count' => Wilayah::query()->where('tipe', Wilayah::TIPE_DESA)->count(),
                'population_total' => $populationTotal > 0 ? $populationTotal : null,
                'household_total' => $householdTotal > 0 ? $householdTotal : null,
                'population_year' => collect($headlineRows)->where('dataset_code', '3.13')->max('tahun'),
                'health_indicators' => $healthIndicators,
            ],
            'sectors' => $sectors,
            'rpjmd' => [
                'datasets' => $indicatorSets->map($datasetCard)->all(),
                'indicators' => $indicators,
                'trend' => $indicatorTrend,
                'published_values' => count($indicatorRows),
            ],
            'apbd' => [
                'datasets' => $budgetSets->map($datasetCard)->all(),
                'items' => $budgetItems,
                'financial_trend' => $budgetTrend,
                'published_values' => count($budgetRows),
                'pagu' => $budgetPagu,
                'realization' => $budgetRealization,
                'financial_progress' => $budgetProgress,
                'physical_realization' => $budgetPhysical,
                'physical_target' => $budgetTarget,
            ],
            'infrastructure' => [
                'inventory' => $infrastructureInventory,
                'map_points' => $mapPoints,
                'mobile_points' => Schema::hasTable('mobile_field_points') ? DB::table('mobile_field_points')->count() : 0,
            ],
            'territory' => [
                'districts' => $districts,
            ],
            'reports' => [
                'opds' => $reportRows,
                'cadence' => $cadence,
                'history' => $history,
            ],
            'demo' => $this->getBupatiDemoRecords(),
        ];
    }

    /** Draft-only samples for preview. They never enter official metrics. */
    private function getBupatiDemoRecords(): array
    {
        $rows = DB::table('dataset_values as v')
            ->join('datasets as d', 'd.id', '=', 'v.dataset_id')
            ->where('v.status', DatasetValue::STATUS_DRAFT)
            ->whereNull('v.published_at')
            ->whereNull('v.deleted_at')
            ->where('v.notes', 'like', '[DEMO-BUPATI]%')
            ->select('d.kode as dataset_code', 'v.tahun', 'v.periode_label', 'v.values as payload', 'v.notes')
            ->orderBy('v.tahun')
            ->get()
            ->map(function ($row): array {
                $row = (array) $row;
                $row['values'] = json_decode((string) $row['payload'], true) ?: [];
                unset($row['payload']);
                return $row;
            });

        return [
            'rpjmd' => $rows->where('dataset_code', 'IKU_DAERAH')->values()->all(),
            'apbd' => $rows->where('dataset_code', '7.73')->values()->all(),
            'infrastructure' => $rows->filter(fn (array $row): bool => in_array($row['dataset_code'], ['9.91', 'DEMO_INFRA_DIGITAL_BUPATI'], true))->values()->all(),
            'is_active' => $rows->isNotEmpty(),
        ];
    }

    /** Build infographic charts from published records and their source metadata. */
    public function getOfficialInfographics(array $datasetCodes): array
    {
        if ($datasetCodes === []) return [];

        $datasets = Dataset::query()
            ->where('is_active', true)
            ->whereIn('kode', $datasetCodes)
            ->with(['opd', 'fields', 'values' => fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('published_at')
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))
                ->orderBy('tahun')->orderBy('id')])
            ->get();
        $charts = app(\App\Services\DynamicChartService::class);
        $presentation = app(\App\Services\InfographicPresentationService::class);

        return $datasets->map(function (Dataset $dataset) use ($charts, $presentation): array {
            $records = $dataset->values;
            return [
                'dataset' => $dataset,
                'published_count' => $records->count(),
                'charts' => $records->isEmpty() ? [] : $charts->statisticsForDataset($dataset, $records),
                'context' => $presentation->context($dataset, $records),
            ];
        })->all();
    }

    /**
     * Overview stats
     */
    public function getOverviewStats(?int $opdFilter = null): array
    {
        $valuesQuery = DB::table('dataset_values')
            ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'));
        $datasetsQuery = DB::table('datasets')->whereNull('deleted_at');

        if ($opdFilter) {
            $valuesQuery->whereIn('dataset_id', function ($query) use ($opdFilter) {
                $query->select('id')->from('datasets')->where('opd_id', $opdFilter)->whereNull('deleted_at');
            });
            $datasetsQuery->where('opd_id', $opdFilter);
        }

        return [
            'total_opd' => Opd::where('aktif', true)->count(),
            'total_datasets' => $datasetsQuery->count(),
            'total_values' => $valuesQuery->count(),
            'published' => (clone $valuesQuery)->where('status', 'published')->count(),
            'verified' => (clone $valuesQuery)->where('status', 'verified')->count(),
            'submitted' => (clone $valuesQuery)->where('status', 'submitted')->count(),
            'draft' => (clone $valuesQuery)->where('status', 'draft')->count(),
            'rejected' => (clone $valuesQuery)->where('status', 'rejected')->count(),
            'completion_rate' => $this->getCompletionRate($opdFilter),
            'kecamatans' => DB::table('wilayahs')->where('tipe', 'kecamatan')->count(),
        ];
    }

    public function getStatsByOpd(int $limit = 10, ?int $opdFilter = null): array
    {
        return DB::table('opds as o')
            ->leftJoin('datasets as d', 'd.opd_id', '=', 'o.id')
            ->leftJoin('dataset_values as dv', 'dv.dataset_id', '=', 'd.id')
            ->where('o.aktif', true)
            ->whereNull('d.deleted_at')
            ->where(fn ($notes) => $notes->whereNull('dv.notes')->orWhere('dv.notes', 'not like', '[DEMO-%'))
            ->when($opdFilter, fn($q) => $q->where('o.id', $opdFilter))
            ->groupBy('o.id', 'o.kode', 'o.nama')
            ->select(
                'o.id', 'o.kode', 'o.nama',
                DB::raw('COUNT(DISTINCT d.id) as total_datasets'),
                DB::raw('COUNT(dv.id) as total_values'),
                DB::raw('SUM(CASE WHEN dv.status = "published" THEN 1 ELSE 0 END) as published')
            )
            ->orderByDesc('total_values')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getStatsByStatus(?int $opdFilter = null): array
    {
        $q = DB::table('dataset_values as dv');
        $q->where(fn ($notes) => $notes->whereNull('dv.notes')->orWhere('dv.notes', 'not like', '[DEMO-%'));
        if ($opdFilter) {
            $q->join('datasets as d', 'd.id', '=', 'dv.dataset_id')->where('d.opd_id', $opdFilter)->whereNull('d.deleted_at');
        }
        return $q->select('dv.status', DB::raw('COUNT(*) as total'))
            ->groupBy('dv.status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function getStatsByKategori(?int $opdFilter = null): array
    {
        try {
            $rows = DB::table('datasets as d')
                ->leftJoin('dataset_values as dv', 'dv.dataset_id', '=', 'd.id')
                ->whereNull('d.deleted_at')
                ->where(fn ($notes) => $notes->whereNull('dv.notes')->orWhere('dv.notes', 'not like', '[DEMO-%'))
                ->when($opdFilter, fn($x) => $x->where('d.opd_id', $opdFilter))
                ->groupBy('d.kategori')
                ->select(
                    DB::raw('COALESCE(d.kategori, "Lainnya") as kategori'),
                    DB::raw('COUNT(DISTINCT d.id) as datasets'),
                    DB::raw('COUNT(dv.id) as total_data')
                )
                ->orderByDesc('datasets')
                ->limit(20)
                ->get()
                ->toArray();
            return $rows ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getRecentActivity(int $limit = 10, ?int $opdFilter = null)
    {
        return DB::table('workflow_logs as w')
            ->leftJoin('users as u', 'u.id', '=', 'w.actor_id')
            ->leftJoin('dataset_values as dv', 'dv.id', '=', 'w.data_value_id')
            ->leftJoin('datasets as d', 'd.id', '=', 'dv.dataset_id')
            ->when($opdFilter, fn($q) => $q->where('d.opd_id', $opdFilter))
            ->select(
                'w.from_status', 'w.to_status', 'w.created_at', 'w.notes',
                'u.name as user_name', 'd.nama as dataset_nama', 'd.kode as dataset_kode'
            )
            ->orderByDesc('w.created_at')
            ->limit($limit)
            ->get();
    }

    public function getTopDatasetsByData(int $limit = 10, ?int $opdFilter = null)
    {
        return DB::table('datasets as d')
            ->leftJoin('dataset_values as dv', 'dv.dataset_id', '=', 'd.id')
            ->whereNull('d.deleted_at')
            ->where(fn ($notes) => $notes->whereNull('dv.notes')->orWhere('dv.notes', 'not like', '[DEMO-%'))
            ->when($opdFilter, fn($q) => $q->where('d.opd_id', $opdFilter))
            ->groupBy('d.id', 'd.kode', 'd.nama', 'd.opd_id')
            ->select(
                'd.id', 'd.kode', 'd.nama', 'd.opd_id',
                DB::raw('COUNT(dv.id) as total_values'),
                DB::raw('SUM(CASE WHEN dv.status = "published" THEN 1 ELSE 0 END) as published')
            )
            ->having('total_values', '>', 0)
            ->orderByDesc('total_values')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getPeriodCoverage(?int $opdFilter = null): array
    {
        $q = DB::table('datasets')->whereNull('deleted_at');
        if ($opdFilter) $q->where('opd_id', $opdFilter);
        return $q->select('period_type', DB::raw('COUNT(*) as total'))
            ->groupBy('period_type')
            ->pluck('total', 'period_type')
            ->toArray();
    }

    /**
     * Calculate completion rate (% published dari total)
     */
    protected function getCompletionRate(?int $opdFilter = null): float
    {
        $q = DB::table('dataset_values as dv');
        $q->where(fn ($notes) => $notes->whereNull('dv.notes')->orWhere('dv.notes', 'not like', '[DEMO-%'));
        if ($opdFilter) {
            $q->join('datasets as d', 'd.id', '=', 'dv.dataset_id')->where('d.opd_id', $opdFilter)->whereNull('d.deleted_at');
        }
        $total = $q->count();
        if ($total === 0) return 0;
        $published = (clone $q)->where('status', 'published')->count();
        return round(($published / $total) * 100, 1);
    }
}
