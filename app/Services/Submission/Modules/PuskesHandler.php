<?php

namespace App\Services\Submission\Modules;

use App\Models\FasilitasKesehatan;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handler for puskesmas/puskes health facility data modules.
 *
 * Covers:
 *  - Stok Obat (inventory)
 *  - Rekam Medis (medical records)
 *  - Tenaga Kesehatan (health workers)
 */
class PuskesHandler extends BaseModuleHandler
{
    public function key(): string
    {
        return 'puskes';
    }
    public function label(): string
    {
        return 'Puskesmas/Puskes';
    }
    public function periodType(): string
    {
        return self::MONTHLY;
    }
    public function entityType(): string
    {
        return FasilitasKesehatan::class;
    }
    public function table(): string
    {
        return 'fasilitas_kesehatans';
    }

    public function context(?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        return [
            'periode_bulan' => $periodeBulan ?? now()->month,
            'semester' => null,
            'periode_tahun' => $periodeTahun ?? now()->year,
        ];
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): Builder
    {
        return FasilitasKesehatan::query()
            ->where('kecamatan_id', $kecamatanId)
            ->where('status_operasional', true);
    }

    /**
     * Snapshot for puskes includes facility info + related child records.
     * Uses $context instead of request() for CLI/API/Queue compatibility.
     */
    public function snapshotRows($records, array $context = []): array
    {
        $periodeBulan = $context['periode_bulan'] ?? now()->month;
        $periodeTahun = $context['periode_tahun'] ?? now()->year;

        return $records->map(function (FasilitasKesehatan $faskes) use ($periodeBulan, $periodeTahun) {
            return [
                'entity_id' => $faskes->getKey(),
                'attributes' => $faskes->getAttributes(),
                'fasilitas' => [
                    'nama' => $faskes->nama,
                    'kode_faskes' => $faskes->kode_faskes,
                    'jenis' => $faskes->jenis,
                    'alamat' => $faskes->alamat,
                    'kepala_puskes' => $faskes->kepala_puskes,
                    'jumlah_posyandu' => $faskes->jumlah_posyandu,
                    'jumlah_pustu' => $faskes->jumlah_pustu,
                    'jumlah_kader' => $faskes->jumlah_kader,
                ],
                'stok_obat' => $faskes->stokObats
                    ->map(fn ($r) => $r->getAttributes())->values()->all(),
                'rekam_medis' => $faskes->rekamMedis()
                    ->whereMonth('tanggal_kunjungan', $periodeBulan)
                    ->whereYear('tanggal_kunjungan', $periodeTahun)
                    ->get()
                    ->map(fn ($r) => $r->getAttributes())->values()->all(),
                'tenaga_kesehatan' => $faskes->tenagaKesehatans
                    ->map(fn ($r) => $r->getAttributes())->values()->all(),
                'statistik' => [
                    'total_stok' => $faskes->stokObats()->count(),
                    'stok_menipis' => $faskes->stokObats()->whereColumn('stok_akhir', '<=', 'stok_minimum')->count(),
                    'total_kunjungan' => $faskes->rekamMedis()
                        ->whereMonth('tanggal_kunjungan', $periodeBulan)
                        ->whereYear('tanggal_kunjungan', $periodeTahun)
                        ->count(),
                    'total_nakes' => $faskes->tenagaKesehatans()->count(),
                ],
            ];
        })->values()->all();
    }
}
