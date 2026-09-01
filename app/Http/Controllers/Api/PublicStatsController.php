<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PublicStatsController — Public API v1 endpoints.
 *
 * PUBLIC API: Arsitektur §12.1
 * Read-only endpoints that expose aggregated, APPROVED data to the public.
 * No authentication required.
 *
 * IMPORTANT: These endpoints ONLY read from APPROVED batches (Arsitektur §4.3).
 * Raw NIK and sensitive personal data are masked per §10 (Portal Publik).
 */
class PublicStatsController extends Controller
{
    /**
     * GET /api/v1/stats
     * Aggregated statistics across all approved data.
     */
    public function stats(Request $request): JsonResponse
    {
        $kabupaten = \App\Models\Kabupaten::query()->first();
        $totalKecamatan = Kecamatan::query()->count();
        $totalDesa = Desa::query()->count();
        $totalPenduduk = Penduduk::query()->inApprovedBatch()->count();
        $totalKeluarga = Keluarga::query()->inApprovedBatch()->count();
        $totalRumah = Rumah::query()->inApprovedBatch()->count();

        // Gender distribution
        $male = Penduduk::query()->inApprovedBatch()->where('jenis_kelamin', 'Laki-laki')->count();
        $female = Penduduk::query()->inApprovedBatch()->where('jenis_kelamin', 'Perempuan')->count();

        // Age distribution
        $today = now();
        $ageGroups = $this->getAgeDistribution();

        // Education distribution (top 5)
        $education = Penduduk::query()
            ->inApprovedBatch()
            ->whereNotNull('pendidikan')
            ->where('pendidikan', '!=', '')
            ->selectRaw('pendidikan, COUNT(*) as total')
            ->groupBy('pendidikan')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->pluck('total', 'pendidikan');

        // Religion distribution
        $religion = Penduduk::query()
            ->inApprovedBatch()
            ->whereNotNull('agama')
            ->where('agama', '!=', '')
            ->selectRaw('agama, COUNT(*) as total')
            ->groupBy('agama')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'agama');

        // Top pekerjaan (top 5)
        $jobs = Penduduk::query()
            ->inApprovedBatch()
            ->whereNotNull('pekerjaan')
            ->where('pekerjaan', '!=', '')
            ->selectRaw('pekerjaan, COUNT(*) as total')
            ->groupBy('pekerjaan')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->pluck('total', 'pekerjaan');

        // Kecamatan summary
        $kecamatanSummary = $this->getKecamatanSummary();

        return response()->json([
            'kabupaten' => $kabupaten?->nama ?? 'Kabupaten Kepulauan Sangihe',
            'kecamatan' => $totalKecamatan,
            'desa' => $totalDesa,
            'penduduk' => $totalPenduduk,
            'keluarga' => $totalKeluarga,
            'rumah' => $totalRumah,
            'gender' => [
                'laki_laki' => $male,
                'perempuan' => $female,
                'rasio' => $female > 0 ? round($male / $female, 2) : null,
            ],
            'age_groups' => $ageGroups,
            'education' => $education,
            'religion' => $religion,
            'jobs' => $jobs,
            'kecamatan' => $kecamatanSummary,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/v1/penduduk
     * Aggregated penduduk statistics (no raw personal data).
     */
    public function penduduk(Request $request): JsonResponse
    {
        $query = Penduduk::query()->inApprovedBatch();

        // Filter by kecamatan
        if ($request->filled('kecamatan_id')) {
            $query->whereHas('keluarga.rumah.desa', fn ($q) =>
                $q->where('kecamatan_id', $request->integer('kecamatan_id'))
            );
        }

        // Filter by desa
        if ($request->filled('desa_id')) {
            $query->whereHas('keluarga.rumah', fn ($q) =>
                $q->where('desa_id', $request->integer('desa_id'))
            );
        }

        $total = $query->count();
        $male = (clone $query)->where('jenis_kelamin', 'Laki-laki')->count();
        $female = (clone $query)->where('jenis_kelamin', 'Perempuan')->count();

        $religion = (clone $query)
            ->whereNotNull('agama')
            ->selectRaw('agama, COUNT(*) as total')
            ->groupBy('agama')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'agama');

        $maritalStatus = (clone $query)
            ->whereNotNull('status_perkawinan')
            ->selectRaw('status_perkawinan, COUNT(*) as total')
            ->groupBy('status_perkawinan')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'status_perkawinan');

        $education = (clone $query)
            ->whereNotNull('pendidikan')
            ->where('pendidikan', '!=', '')
            ->selectRaw('pendidikan, COUNT(*) as total')
            ->groupBy('pendidikan')
            ->orderByDesc('total')
            ->get()
            ->pluck('total', 'pendidikan');

        $ageGroups = $this->getAgeDistributionForQuery($query);

        return response()->json([
            'total' => $total,
            'gender' => [
                'laki_laki' => $male,
                'perempuan' => $female,
            ],
            'religion' => $religion,
            'marital_status' => $maritalStatus,
            'education' => $education,
            'age_groups' => $ageGroups,
        ]);
    }

    /**
     * GET /api/v1/kecamatan
     * List all kecamatan with aggregate counts.
     */
    public function kecamatan(Request $request): JsonResponse
    {
        $kecamatans = Kecamatan::query()
            ->withCount(['desas as total_desa'])
            ->get()
            ->map(function (Kecamatan $kecamatan): array {
                $rumahCount = Rumah::query()
                    ->inApprovedBatch()
                    ->whereHas('desa', fn ($q) => $q->where('kecamatan_id', $kecamatan->id))
                    ->count();

                $pendudukCount = Penduduk::query()
                    ->inApprovedBatch()
                    ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatan->id))
                    ->count();

                return [
                    'id' => $kecamatan->id,
                    'nama' => $kecamatan->nama,
                    'total_desa' => (int) ($kecamatan->total_desa ?? $kecamatan->desas?->count() ?? 0),
                    'total_rumah' => $rumahCount,
                    'total_penduduk' => $pendudukCount,
                ];
            });

        return response()->json([
            'kecamatan' => $kecamatans,
            'total' => $kecamatans->count(),
        ]);
    }

    /**
     * GET /api/v1/desa
     * List all desa with aggregate counts.
     */
    public function desa(Request $request): JsonResponse
    {
        $query = Desa::query()->with('kecamatan:id,nama');

        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->integer('kecamatan_id'));
        }

        $desas = $query->get()->map(function (Desa $desa): array {
            $rumahCount = Rumah::query()
                ->inApprovedBatch()
                ->where('desa_id', $desa->id)
                ->count();

            $pendudukCount = Penduduk::query()
                ->inApprovedBatch()
                ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desa->id))
                ->count();

            return [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'kecamatan_id' => $desa->kecamatan_id,
                'kecamatan' => $desa->kecamatan?->nama,
                'total_rumah' => $rumahCount,
                'total_penduduk' => $pendudukCount,
            ];
        });

        return response()->json([
            'desa' => $desas,
            'total' => $desas->count(),
        ]);
    }

    /**
     * GET /api/v1/kecamatan/{id}/desa
     * Get desa by kecamatan.
     */
    public function desaByKecamatan(int $id): JsonResponse
    {
        $kecamatan = Kecamatan::query()->find($id);

        if (! $kecamatan) {
            return response()->json(['message' => 'Kecamatan not found'], 404);
        }

        $desas = $kecamatan->desas()->get()->map(function (Desa $desa): array {
            $rumahCount = Rumah::query()
                ->inApprovedBatch()
                ->where('desa_id', $desa->id)
                ->count();

            return [
                'id' => $desa->id,
                'nama' => $desa->nama,
                'total_rumah' => $rumahCount,
            ];
        });

        return response()->json([
            'kecamatan' => $kecamatan->nama,
            'desa' => $desas,
            'total' => $desas->count(),
        ]);
    }

    // ========================
    // Helpers
    // ========================

    private function getAgeDistribution(): array
    {
        $today = now();
        $ageGroups = [
            '0-4' => 0,
            '5-14' => 0,
            '15-24' => 0,
            '25-34' => 0,
            '35-44' => 0,
            '45-54' => 0,
            '55-64' => 0,
            '65+' => 0,
        ];

        $query = Penduduk::query()->inApprovedBatch();
        foreach ($ageGroups as $label => $count) {
            [$min, $max] = explode('-', $label);
            $maxYears = $max === '+' ? 150 : (int) $max;
            $minDate = $today->copy()->subYears($maxYears)->endOfDay();
            $maxDate = $max === '+'
                ? $today->copy()->subYears(65)->startOfDay()
                : $today->copy()->subYears((int) $min)->startOfDay();

            if ($max === '+') {
                $ageGroups[$label] = (clone $query)
                    ->whereDate('tanggal_lahir', '<=', $minDate)
                    ->count();
            } else {
                $ageGroups[$label] = (clone $query)
                    ->whereBetween('tanggal_lahir', [$minDate, $maxDate])
                    ->count();
            }
        }

        return $ageGroups;
    }

    private function getAgeDistributionForQuery($query): array
    {
        $today = now();
        $ageGroups = [
            '0-4' => 0,
            '5-14' => 0,
            '15-24' => 0,
            '25-34' => 0,
            '35-44' => 0,
            '45-54' => 0,
            '55-64' => 0,
            '65+' => 0,
        ];

        foreach ($ageGroups as $label => $count) {
            [$min, $max] = explode('-', $label);
            $maxYears = $max === '+' ? 150 : (int) $max;
            $minDate = $today->copy()->subYears($maxYears)->endOfDay();
            $maxDate = $max === '+'
                ? $today->copy()->subYears(65)->startOfDay()
                : $today->copy()->subYears((int) $min)->startOfDay();

            if ($max === '+') {
                $ageGroups[$label] = (clone $query)
                    ->whereDate('tanggal_lahir', '<=', $minDate)
                    ->count();
            } else {
                $ageGroups[$label] = (clone $query)
                    ->whereBetween('tanggal_lahir', [$minDate, $maxDate])
                    ->count();
            }
        }

        return $ageGroups;
    }

    private function getKecamatanSummary(): array
    {
        return Kecamatan::query()
            ->withCount(['desas'])
            ->get()
            ->map(fn (Kecamatan $k): array => [
                'id' => $k->id,
                'nama' => $k->nama,
                'total_desa' => (int) ($k->total_desa ?? $k->desas?->count() ?? 0),
                'total_penduduk' => Penduduk::query()
                    ->inApprovedBatch()
                    ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $k->id))
                    ->count(),
            ])
            ->toArray();
    }
}
