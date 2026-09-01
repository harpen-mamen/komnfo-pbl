<?php

namespace App\Services;

/**
 * SPRINT 6: Health Statistics Service
 * 
 * Menghitung statistik kesehatan secara otomatis dari data penduduk.
 * Tidak perlu input manual - sistem otomatis menghitung berdasarkan data.
 */
class HealthStatisticsService
{
    /**
     * Hitung statistik kesehatan untuk kecamatan tertentu.
     */
    public function calculateKecamatanStatistics(int $kecamatanId, int $year, ?int $month = null): array
    {
        return [
            'kecamatan_id' => $kecamatanId,
            'tahun' => $year,
            'bulan' => $month,
            'periode' => $month ? "{$year}-{$month}" : (string) $year,
            'data' => [
                'ibu_hamil' => $this->countIbuHamil($kecamatanId, $year, $month),
                'bayi_lahir' => $this->countBayiLahir($kecamatanId, $year, $month),
                'bayi_0_12_bulan' => $this->countBayi($kecamatanId, $year),
                'balita' => $this->countBalita($kecamatanId, $year),
                'anak_pendek' => $this->countAnakPendek($kecamatanId, $year),
                'ibu_menyusui' => $this->countIbuMenyusui($kecamatanId, $year, $month),
                'lansia' => $this->countLansia($kecamatanId, $year),
                'kematian_ibu' => $this->countKematianIbu($kecamatanId, $year, $month),
                'kematian_bayi' => $this->countKematianBayi($kecamatanId, $year, $month),
            ],
            'puskesmas' => $this->getPuskesmasCount($kecamatanId),
            'tenaga_kesehatan' => $this->countTenagaKesehatan($kecamatanId),
            'calculated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Hitung statistik kesehatan untuk desa tertentu.
     */
    public function calculateDesaStatistics(int $desaId, int $year, ?int $month = null): array
    {
        return [
            'desa_id' => $desaId,
            'tahun' => $year,
            'bulan' => $month,
            'periode' => $month ? "{$year}-{$month}" : (string) $year,
            'data' => [
                'ibu_hamil' => $this->countIbuHamilByDesa($desaId, $year, $month),
                'bayi_lahir' => $this->countBayiLahirByDesa($desaId, $year, $month),
                'bayi_0_12_bulan' => $this->countBayiByDesa($desaId, $year),
                'balita' => $this->countBalitaByDesa($desaId, $year),
                'anak_pendek' => $this->countAnakPendekByDesa($desaId, $year),
                'ibu_menyusui' => $this->countIbuMenyusuiByDesa($desaId, $year, $month),
                'lansia' => $this->countLansiaByDesa($desaId, $year),
            ],
            'calculated_at' => now()->toDateTimeString(),
        ];
    }

    // ==================== IBU HAMIL ====================

    private function countIbuHamil(int $kecamatanId, int $year, ?int $month = null): int
    {
        return $this->countByStatusKehamilan($kecamatanId, 'hamil', $year, $month);
    }

    private function countIbuHamilByDesa(int $desaId, int $year, ?int $month = null): int
    {
        return $this->countByStatusKehamilanByDesa($desaId, 'hamil', $year, $month);
    }

    // ==================== BAYI ====================

    private function countBayiLahir(int $kecamatanId, int $year, ?int $month = null): int
    {
        $query = \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->where('status_kelahiran', 'lahir')
            ->whereYear('tanggal_lahir', $year);

        if ($month) {
            $query->whereMonth('tanggal_lahir', $month);
        }

        return $query->count();
    }

    private function countBayiLahirByDesa(int $desaId, int $year, ?int $month = null): int
    {
        $query = \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->where('status_kelahiran', 'lahir')
            ->whereYear('tanggal_lahir', $year);

        if ($month) {
            $query->whereMonth('tanggal_lahir', $month);
        }

        return $query->count();
    }

    private function countBayi(int $kecamatanId, int $year): int
    {
        $minDate = now()->createFromDate($year)->subMonth(12)->startOfMonth();
        $maxDate = now()->createFromDate($year)->endOfMonth();

        return \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->whereBetween('tanggal_lahir', [$minDate, $maxDate])
            ->where('status_kelahiran', 'lahir')
            ->count();
    }

    private function countBayiByDesa(int $desaId, int $year): int
    {
        $minDate = now()->createFromDate($year)->subMonth(12)->startOfMonth();
        $maxDate = now()->createFromDate($year)->endOfMonth();

        return \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->whereBetween('tanggal_lahir', [$minDate, $maxDate])
            ->where('status_kelahiran', 'lahir')
            ->count();
    }

    // ==================== BALITA ====================

    private function countBalita(int $kecamatanId, int $year): int
    {
        $maxAge = 5; // tahun
        $minBirthYear = $year - $maxAge;

        return \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->whereYear('tanggal_lahir', '>=', $minBirthYear)
            ->whereYear('tanggal_lahir', '<=', $year)
            ->where('status_kelahiran', 'lahir')
            ->count();
    }

    private function countBalitaByDesa(int $desaId, int $year): int
    {
        $maxAge = 5;
        $minBirthYear = $year - $maxAge;

        return \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->whereYear('tanggal_lahir', '>=', $minBirthYear)
            ->whereYear('tanggal_lahir', '<=', $year)
            ->where('status_kelahiran', 'lahir')
            ->count();
    }

    // ==================== ANAK PENDEK (STUNTING) ====================

    private function countAnakPendek(int $kecamatanId, int $year): int
    {
        // Hitung anak dengan tinggi/umur di bawah normal (indikator stunting)
        return \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->whereYear('tanggal_lahir', '>=', $year - 5)
            ->where(function ($q) {
                $q->where('status_gizi', 'pendek')
                    ->orWhere('status_gizi', 'sangat_pendek');
            })
            ->count();
    }

    private function countAnakPendekByDesa(int $desaId, int $year): int
    {
        return \App\Models\Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->whereYear('tanggal_lahir', '>=', $year - 5)
            ->where(function ($q) {
                $q->where('status_gizi', 'pendek')
                    ->orWhere('status_gizi', 'sangat_pendek');
            })
            ->count();
    }
}