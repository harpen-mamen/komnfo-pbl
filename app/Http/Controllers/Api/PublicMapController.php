<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use App\Services\DatasetSpatialDataService;
use App\Services\ThematicMapDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicMapController extends Controller
{
    public function index(Request $request, ThematicMapDataService $thematic, DatasetSpatialDataService $spatial): JsonResponse
    {
        $payload = Cache::remember('public.map.payload.v3', now()->addMinute(), function () use ($thematic, $spatial): array {
            $thematicData = $thematic->forAdminMap();
            return [
                'area_stats' => $thematicData['profiles'],
                'indicators' => $thematicData['indicators'],
                'kecamatans' => $thematicData['kecamatans'],
                'features' => $spatial->publishedFeatureCollection(),
                'summary' => [
                    'penduduk' => $this->publishedAggregate('3.13', ['penduduk_laki', 'penduduk_perempuan', 'total']),
                    'keluarga' => $this->publishedAggregate('3.12', ['kk_laki', 'kk_perempuan', 'total']),
                    'tahun' => $this->publishedYear('3.13'),
                    'kecamatan' => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->count(),
                    'desa' => Wilayah::query()->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->count(),
                ],
            ];
        });
        return response()->json($payload);
    }

    public function search(Request $request, DatasetSpatialDataService $spatial): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        abort_if(mb_strlen($term) < 3, 422, 'Minimal 3 karakter.');
        $features = collect($spatial->publishedFeatureCollection()['features'] ?? [])
            ->filter(fn (array $feature) => str_contains(mb_strtolower((string) data_get($feature, 'properties.name')), mb_strtolower($term)))
            ->take(10)->values();
        return response()->json(['data' => $features]);
    }

    private function publishedAggregate(string $code, array $fields): int|float
    {
        $dataset = \App\Models\Dataset::query()->where('kode', $code)->where('is_active', true)
            ->whereIn('lisensi', [\App\Models\Dataset::LISENSI_CCBY, \App\Models\Dataset::LISENSI_OPEN])
            ->whereHas('opd', fn ($query) => $query->where('kode', 'DUKCAPIL'))->first();
        if (! $dataset) return 0;
        $values = $dataset->values()->where('status', \App\Models\DatasetValue::STATUS_PUBLISHED)
            ->whereNotNull('published_at')->whereNull('deleted_at')
            ->where('wilayah_type', 'kecamatan')
            ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))->get();
        $year = $values->max('tahun');
        return $values->where('tahun', $year)->sum(function ($record) use ($fields) {
            foreach ($fields as $field) if (is_numeric(data_get($record->values, $field))) return (float) data_get($record->values, $field);
            return 0;
        });
    }

    private function publishedYear(string $code): ?int
    {
        return \App\Models\DatasetValue::query()->where('status', \App\Models\DatasetValue::STATUS_PUBLISHED)
            ->whereNotNull('published_at')->whereNull('deleted_at')
            ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))
            ->whereHas('dataset', fn ($query) => $query->where('kode', $code)->where('is_active', true)
                ->whereIn('lisensi', [\App\Models\Dataset::LISENSI_CCBY, \App\Models\Dataset::LISENSI_OPEN])
                ->whereHas('opd', fn ($opd) => $opd->where('kode', 'DUKCAPIL')))->max('tahun');
    }
}
