<?php

namespace App\Services;

use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\Keluarga;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get demographic statistics
     * READ MODEL: Only reads from APPROVED batches (Arsitektur §4.3)
     */
    public static function getDemographicStats($query = null)
    {
        if ($query === null) {
            $query = Penduduk::query()->inApprovedBatch();
        }

        $penduduk = clone $query;
        $total = $penduduk->count();
        $today = Carbon::today();

        return [
            'total' => $total,
            'male' => (clone $query)->where('jenis_kelamin', 'Laki-laki')->count(),
            'female' => (clone $query)->where('jenis_kelamin', 'Perempuan')->count(),
            'avgAge' => round((clone $query)->selectRaw(
                'AVG(YEAR(FROM_DAYS(DATEDIFF(NOW(), tanggal_lahir)))) as avg_age'
            )->first()?->avg_age ?? 0),
            'ageGroups' => [
                'balita' => (clone $query)->whereDate('tanggal_lahir', '>', $today->copy()->subYears(5))->count(),
                'anak' => (clone $query)->whereBetween('tanggal_lahir', 
                    [$today->copy()->subYears(17)->endOfDay(), $today->copy()->subYears(5)->startOfDay()])->count(),
                'dewasa' => (clone $query)->whereBetween('tanggal_lahir',
                    [$today->copy()->subYears(60)->endOfDay(), $today->copy()->subYears(18)->startOfDay()])->count(),
                'lansia' => (clone $query)->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(60))->count(),
            ],
        ];
    }

    /**
     * Get education statistics
     * READ MODEL: Only reads from APPROVED batches (Arsitektur §4.3)
     */
    public static function getEducationStats($query = null)
    {
        if ($query === null) {
            $query = Penduduk::query()->inApprovedBatch();
        }

        return (clone $query)
            ->where('pendidikan', '!=', null)
            ->where('pendidikan', '!=', '')
            ->groupBy('pendidikan')
            ->selectRaw('pendidikan, COUNT(*) as count')
            ->get()
            ->pluck('count', 'pendidikan')
            ->toArray();
    }

    /**
     * Get employment statistics
     * READ MODEL: Only reads from APPROVED batches (Arsitektur §4.3)
     */
    public static function getEmploymentStats($query = null, $limit = 15)
    {
        if ($query === null) {
            $query = Penduduk::query()->inApprovedBatch();
        }

        return (clone $query)
            ->where('pekerjaan', '!=', null)
            ->where('pekerjaan', '!=', '')
            ->groupBy('pekerjaan')
            ->selectRaw('pekerjaan, COUNT(*) as count')
            ->orderByDesc('count')
            ->take($limit)
            ->get()
            ->pluck('count', 'pekerjaan')
            ->toArray();
    }

    /**
     * Get housing statistics
     * READ MODEL: Only reads from APPROVED batches (Arsitektur §4.3)
     */
    public static function getHousingStats($query = null)
    {
        if ($query === null) {
            $query = Rumah::query()->inApprovedBatch();
        }

        $rumah = clone $query;
        $totalRumah = $rumah->count();
        $keluargaTotal = Keluarga::query()->inApprovedBatch()->count();

        return [
            'totalRumah' => $totalRumah,
            'totalKeluarga' => $keluargaTotal,
            'avgKeluargaPerRumah' => $totalRumah > 0 ? round($keluargaTotal / $totalRumah, 2) : 0,
            'byStatus' => (clone $query)
                ->groupBy('status_rumah')
                ->selectRaw('status_rumah, COUNT(*) as count')
                ->get()
                ->pluck('count', 'status_rumah')
                ->toArray(),
        ];
    }

    /**
     * Get religion statistics
     * READ MODEL: Only reads from APPROVED batches (Arsitektur §4.3)
     */
    public static function getReligionStats($query = null)
    {
        if ($query === null) {
            $query = Penduduk::query()->inApprovedBatch();
        }

        return (clone $query)
            ->where('agama', '!=', null)
            ->groupBy('agama')
            ->selectRaw('agama, COUNT(*) as count')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'agama')
            ->toArray();
    }

    /**
     * Get marital status statistics
     * READ MODEL: Only reads from APPROVED batches (Arsitektur §4.3)
     */
    public static function getMaritalStatusStats($query = null)
    {
        if ($query === null) {
            $query = Penduduk::query()->inApprovedBatch();
        }

        return (clone $query)
            ->where('status_perkawinan', '!=', null)
            ->groupBy('status_perkawinan')
            ->selectRaw('status_perkawinan, COUNT(*) as count')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'status_perkawinan')
            ->toArray();
    }

    /**
     * Get income distribution
     * READ MODEL: Only reads from APPROVED batches (Arsitektur §4.3)
     */
    public static function getIncomeDistribution($query = null)
    {
        if ($query === null) {
            $query = Penduduk::query()->inApprovedBatch();
        }

        $ranges = [
            '< 1 Juta' => [0, 1000000],
            '1-2 Juta' => [1000000, 2000000],
            '2-3 Juta' => [2000000, 3000000],
            '3-5 Juta' => [3000000, 5000000],
            '5-10 Juta' => [5000000, 10000000],
            '> 10 Juta' => [10000000, PHP_INT_MAX],
        ];

        $distribution = [];
        foreach ($ranges as $label => $range) {
            $distribution[$label] = (clone $query)
                ->whereBetween('penghasilan', $range)
                ->count();
        }

        
        return $distribution;
    }

    /**
     * Get comprehensive analytics summary
     */
    public static function getComprehensiveSummary($pendudukQuery = null, $rumahQuery = null)
    {
        return [
            'demographics' => self::getDemographicStats($pendudukQuery),
            'education' => self::getEducationStats($pendudukQuery),
            'employment' => self::getEmploymentStats($pendudukQuery),
            'housing' => self::getHousingStats($rumahQuery),
            'religion' => self::getReligionStats($pendudukQuery),
            'maritalStatus' => self::getMaritalStatusStats($pendudukQuery),
            'incomeDistribution' => self::getIncomeDistribution($pendudukQuery),
        ];
    }
}
