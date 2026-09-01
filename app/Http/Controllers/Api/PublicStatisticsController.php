<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataKesehatanBulanan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicStatisticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payload = Cache::remember('public.statistics', now()->addMinutes(5), function (): array {
            $totalPenduduk = (int) Penduduk::query()->inApprovedBatch()->count();
            $totalKeluarga = (int) Keluarga::query()->inApprovedBatch()->count();
            $totalRumah    = (int) Rumah::query()->inApprovedBatch()->count();

            // Aggregate by gender (jk is L/P)
            $byGender = Penduduk::query()->inApprovedBatch()
                ->selectRaw('jenis_kelamin, COUNT(*) as total')
                ->groupBy('jenis_kelamin')
                ->pluck('total', 'jenis_kelamin');

            // Aggregate by age bracket
            $byAge = Penduduk::query()->inApprovedBatch()
                ->selectRaw("
                    CASE
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 5  THEN '0-4'
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 12 THEN '5-11'
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 18 THEN '12-17'
                        WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 60 THEN '18-59'
                        ELSE '60+'
                    END as bracket,
                    COUNT(*) as total
                ")
                ->whereNotNull('tanggal_lahir')
                ->groupBy('bracket')
                ->pluck('total', 'bracket');

            // Top diseases (last 90 days)
            $diseaseTotals = Cache::remember('public.statistics.disease', now()->addHour(), function (): array {
                $rows = DataKesehatanBulanan::query()->inApprovedBatch()->get();
                return [
                    'ispa' => (int) $rows->sum('ispa'),
                    'dbd' => (int) $rows->sum('dbd'),
                    'malaria' => (int) $rows->sum('malaria'),
                    'tbc' => (int) $rows->sum('tbc'),
                    'hipertensi' => (int) $rows->sum('hipertensi'),
                    'diabetes' => (int) $rows->sum('diabetes'),
                    'stunting' => (int) $rows->sum('stunting'),
                    'gizi_buruk' => (int) $rows->sum('gizi_buruk'),
                ];
            });

            return [
                'total' => [
                    'penduduk' => $totalPenduduk,
                    'keluarga' => $totalKeluarga,
                    'rumah'    => $totalRumah,
                ],
                'by_gender' => $byGender,
                'by_age'    => $byAge,
                'diseases'  => $diseaseTotals,
            ];
        });

        return response()->json($payload);
    }
}
