<?php

namespace App\Services;

use App\Models\TenagaKesehatan;

/** Ringkasan hanya berasal dari laporan kesehatan yang telah dipublikasikan Kominfo. */
class HealthStatisticsService
{
    public function calculateKecamatanStatistics(int $kecamatanId, int $year, ?int $month = null): array
    {
        // DataKesehatanBulanan table removed - return empty data
        return [
            'kecamatan_id' => $kecamatanId,
            'tahun' => $year,
            'bulan' => $month,
            'periode' => $month ? "{$year}-{$month}" : (string) $year,
            'data' => [
                'ibu_hamil' => 0,
                'bayi_lahir' => 0,
                'bayi_0_12_bulan' => 0,
                'balita' => 0,
                'anak_pendek' => 0,
                'tenaga_kesehatan' => 0,
            ],
            'laporan_terverifikasi' => 0,
            'calculated_at' => now()->toDateTimeString(),
        ];
    }
}
