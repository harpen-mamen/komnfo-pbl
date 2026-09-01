<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataKesehatanBulanan;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Penduduk;
use App\Models\Rumah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicMapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payload = Cache::remember('public.map.payload', now()->addMinutes(5), function (): array {
            $areaStats = Kecamatan::query()
                ->withCount(['desas as rumah_terdata' => fn ($q) => $q->whereHas('rumah', fn ($r) => $r->inApprovedBatch())])
                ->withCount(['desas as penduduk_terdata' => fn ($q) => $q->whereHas('rumah.keluarga.penduduk', fn ($p) => $p->inApprovedBatch())])
                ->get(['id', 'nama'])
                ->mapWithKeys(fn ($k) => [
                    mb_strtolower($k->nama) => [
                        'rumah' => (int) $k->rumah_terdata,
                        'penduduk' => (int) $k->penduduk_terdata,
                    ]
                ]);

            $fields = ['ispa', 'dbd', 'malaria', 'tbc', 'hipertensi', 'diabetes', 'covid', 'lainnya', 'stunting', 'gizi_buruk'];
            $diseaseData = DataKesehatanBulanan::query()->inApprovedBatch()
                ->with('kecamatan')
                ->get()
                ->groupBy(fn ($row) => mb_strtolower($row->kecamatan?->nama ?? ''))
                ->map(fn ($rows) => collect($fields)->mapWithKeys(fn ($field) => [$field => (int) $rows->sum($field)])->all());

            return [
                'area_stats' => $areaStats,
                'disease_data' => $diseaseData,
                'summary' => [
                    'penduduk'  => Penduduk::query()->inApprovedBatch()->count(),
                    'rumah'     => Rumah::query()->inApprovedBatch()->count(),
                    'kecamatan' => Kecamatan::query()->count(),
                ],
            ];
        });

        return response()->json($payload);
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q'));
        abort_if(mb_strlen($term) < 3, 422, 'Minimal 3 karakter.');

        $results = Rumah::query()
            ->where('publik', true)
            ->inApprovedBatch()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->whereHas('keluarga', fn ($q) => $q->where('nama_kepala_keluarga', 'like', '%' . $term . '%'))
            ->with(['desa.kecamatan', 'keluarga' => fn ($q) => $q->select('id', 'rumah_id', 'nama_kepala_keluarga')])
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->keluarga->pluck('nama_kepala_keluarga')->filter()->first(),
                'desa' => $r->desa?->nama,
                'kecamatan' => $r->desa?->kecamatan?->nama,
                'lat' => (float) $r->latitude,
                'lng' => (float) $r->longitude,
            ])
            ->values();

        return response()->json(['data' => $results]);
    }
}
