<?php

namespace App\Services;

use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\Wilayah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAggregationService
{
    /**
     * Fokus OPD: Return empty collection karena tabel rumah/keluarga/penduduk belum ada.
     * Alur mobile dan admin kecamatan dinonaktifkan sementara.
     */
    public function kecamatanRows(): Collection
    {
        // Return empty - mobile flow disabled, focus on OPD
        return collect();
    }

    /**
     * Get aggregated stats for OPD dashboard.
     * Returns counts from datasets/values instead of rumah/keluarga/penduduk.
     */
    public function getOpdStats(): array
    {
        $datasets = Dataset::query()->count();
        $datasetValues = DatasetValue::query()->count();
        $opds = Opd::query()->where('aktif', true)->count();

        // Get values count grouped by dataset (through join)
        $valuesByDataset = DB::table('dataset_values')
            ->join('datasets', 'dataset_values.dataset_id', '=', 'datasets.id')
            ->selectRaw('datasets.id as dataset_id, COUNT(dataset_values.id) as total')
            ->groupBy('datasets.id')
            ->pluck('total', 'dataset_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        return [
            'datasets' => $datasets,
            'dataset_values' => $datasetValues,
            'opds' => $opds,
            'values_by_dataset' => $valuesByDataset,
        ];
    }

    /**
     * Get wilayah stats - kecamatan and desa counts.
     */
    public function getWilayahStats(): array
    {
        $kecamatans = Wilayah::query()
            ->where('tipe', Wilayah::TIPE_KECAMATAN)
            ->count();

        $desas = Wilayah::query()
            ->where('tipe', Wilayah::TIPE_DESA)
            ->count();

        return [
            'kecamatan' => $kecamatans,
            'desa' => $desas,
        ];
    }
}
