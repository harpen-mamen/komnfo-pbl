<?php

namespace App\Services\Submission\Modules;

use App\Models\DataKesehatanBulanan;

class KesehatanHandler extends BaseModuleHandler
{
    public function key(): string { return 'kesehatan'; }
    public function label(): string { return 'Kesehatan'; }
    public function periodType(): string { return self::MONTHLY; }
    public function entityType(): string { return DataKesehatanBulanan::class; }
    public function table(): string { return 'data_kesehatan_bulanans'; }

    public function context(?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        return [
            'periode_bulan' => $periodeBulan ?? now()->month,
            'semester' => null,
            'periode_tahun' => $periodeTahun ?? now()->year,
        ];
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): \Illuminate\Database\Eloquent\Builder
    {
        return DataKesehatanBulanan::query()
            ->where('kecamatan_id', $kecamatanId)
            ->when(($context['periode_bulan'] ?? null) !== null, fn ($query) => $query->where('periode_bulan', $context['periode_bulan']))
            ->when(($context['periode_tahun'] ?? null) !== null, fn ($query) => $query->where('periode_tahun', $context['periode_tahun']));
    }

    public function snapshotRows($records, array $context = []): array
    {
        return $records->map(function ($record): array {
            $record->loadMissing([
                'ibuHamils', 'bayis', 'tenagaKesehatans', 'pelayananKesehatans', 'kasusPenyakit',
            ]);

            return [
                'entity_id' => $record->getKey(),
                'attributes' => $record->getAttributes(),
                'ibu_hamil' => $record->ibuHamils->map(fn ($r) => $r->getAttributes())->values()->all(),
                'bayi' => $record->bayis->map(fn ($r) => $r->getAttributes())->values()->all(),
                'tenaga_kesehatan' => $record->tenagaKesehatans->map(fn ($r) => $r->getAttributes())->values()->all(),
                'pelayanan_kesehatan' => $record->pelayananKesehatans->map(fn ($r) => $r->getAttributes())->values()->all(),
                'kasus_penyakit' => $record->kasusPenyakit->map(fn ($r) => $r->getAttributes())->values()->all(),
            ];
        })->values()->all();
    }
}
