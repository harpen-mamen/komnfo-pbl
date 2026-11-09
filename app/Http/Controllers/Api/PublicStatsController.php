<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/** Public aggregate endpoints backed by published open datasets and the wilayah master. */
class PublicStatsController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $values = $this->openPublishedValues();
        $population = $this->latestIndicator($values, '3.13', ['penduduk_laki', 'penduduk_perempuan', 'total']);
        $households = $this->latestIndicator($values, '3.12', ['kk_laki', 'kk_perempuan', 'total']);

        return response()->json([
            'kabupaten' => Wilayah::query()->where('tipe', Wilayah::TIPE_KABUPATEN)->where('aktif', true)->value('nama') ?? 'Kabupaten Kepulauan Sangihe',
            'kecamatan_total' => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->count(),
            'desa_total' => Wilayah::query()->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->count(),
            'penduduk' => $population,
            'keluarga' => $households,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function penduduk(Request $request): JsonResponse
    {
        $values = $this->openPublishedValues()->filter(fn (DatasetValue $value) => $this->datasetCode($value) === '3.13');
        if ($request->filled('kecamatan_id')) {
            $values = $values->filter(fn (DatasetValue $value) => $value->wilayah_type === Wilayah::TIPE_KECAMATAN && (int) $value->wilayah_id === $request->integer('kecamatan_id'));
        } elseif ($request->filled('desa_id')) {
            return response()->json(['total' => null, 'data' => [], 'tahun' => null, 'message' => 'Data penduduk publik tersedia pada tingkat kecamatan.']);
        }
        $latestYear = $values->max('tahun');
        $values = $values->where('tahun', $latestYear);
        $totals = [];
        foreach ($values->where('tahun', $latestYear) as $record) {
            $count = $this->recordTotal($record, ['penduduk_laki', 'penduduk_perempuan', 'total']);
            if ($count === null) continue;
            $key = (string) ($record->wilayah_type === Wilayah::TIPE_KECAMATAN ? $record->wilayah_id : 'kabupaten');
            $totals[$key] = ($totals[$key] ?? 0) + $count;
        }
        return response()->json(['total' => array_sum($totals), 'data' => $totals, 'tahun' => $latestYear]);
    }

    public function kecamatan(Request $request): JsonResponse
    {
        $districts = Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->withCount([
            'children as total_desa' => fn ($query) => $query->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true),
        ])->orderBy('nama')->get();
        $values = $this->openPublishedValues()->filter(fn (DatasetValue $value) => $value->wilayah_type === Wilayah::TIPE_KECAMATAN);
        $population = $this->latestIndicator($values, '3.13', ['penduduk_laki', 'penduduk_perempuan', 'total']);
        $households = $this->latestIndicator($values, '3.12', ['kk_laki', 'kk_perempuan', 'total']);

        $data = $districts->map(fn (Wilayah $district) => [
            'id' => $district->id,
            'nama' => $district->nama,
            'total_desa' => (int) $district->total_desa,
            'total_penduduk' => $population['by_wilayah'][$district->id] ?? null,
            'total_keluarga' => $households['by_wilayah'][$district->id] ?? null,
        ]);
        return response()->json(['kecamatan' => $data, 'total' => $data->count()]);
    }

    public function desa(Request $request): JsonResponse
    {
        $query = Wilayah::query()->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true);
        if ($request->filled('kecamatan_id')) $query->where('parent_id', $request->integer('kecamatan_id'));
        $data = $query->with('parent:id,nama')->orderBy('nama')->get()->map(fn (Wilayah $v) => [
            'id' => $v->id, 'nama' => $v->nama, 'kecamatan_id' => $v->parent_id, 'kecamatan' => $v->parent?->nama,
        ]);
        return response()->json(['desa' => $data, 'total' => $data->count()]);
    }

    public function desaByKecamatan(int $id): JsonResponse
    {
        $district = Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->find($id);
        if (! $district) return response()->json(['message' => 'Kecamatan tidak ditemukan'], 404);
        $villages = Wilayah::query()->where('parent_id', $district->id)->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->orderBy('nama')->get(['id', 'nama']);
        return response()->json(['kecamatan' => $district->nama, 'desa' => $villages, 'total' => $villages->count()]);
    }

    private function openPublishedValues(): Collection
    {
        return DatasetValue::query()->where('status', DatasetValue::STATUS_PUBLISHED)->whereNotNull('published_at')
            ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))
            ->whereHas('dataset', fn ($query) => $query->where('is_active', true)
                ->whereIn('lisensi', [Dataset::LISENSI_CCBY, Dataset::LISENSI_OPEN])->whereIn('kode', ['3.12', '3.13'])
                ->whereHas('opd', fn ($opd) => $opd->where('kode', 'DUKCAPIL')))
            ->with('dataset:id,kode,kode_sdi,nama')->get();
    }

    private function latestIndicator(Collection $values, string $code, array $preferredFields): array
    {
        $selected = $values->filter(fn (DatasetValue $value) => $this->datasetCode($value) === $code);
        $latestYear = $selected->max('tahun');
        $selected = $selected->filter(fn (DatasetValue $value) => $value->tahun === $latestYear);
        $totals = [];
        foreach ($selected as $record) {
            $recordTotal = $this->recordTotal($record, $preferredFields);
            if ($recordTotal === null) continue;
            $key = (string) ($record->wilayah_type === Wilayah::TIPE_KECAMATAN ? $record->wilayah_id : 'kabupaten');
            $totals[$key] = ($totals[$key] ?? 0) + $recordTotal;
        }
        return ['total' => array_sum($totals), 'by_wilayah' => $totals, 'tahun' => $latestYear];
    }

    private function datasetCode(DatasetValue $value): string
    {
        return (string) ($value->dataset?->kode_sdi ?: $value->dataset?->kode);
    }

    private function recordTotal(DatasetValue $record, array $preferredFields): int|float|null
    {
        foreach ($preferredFields as $field) {
            $number = data_get($record->values, $field);
            if (is_numeric($number)) return (float) $number;
        }
        return null;
    }
}
