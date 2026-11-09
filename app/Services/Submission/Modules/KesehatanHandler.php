<?php

namespace App\Services\Submission\Modules;

use Illuminate\Database\Eloquent\Builder;
use App\Models\IbuHamil;
use App\Models\Bayi;
use App\Models\TenagaKesehatan;
use App\Models\PelayananKesehatan;
use App\Models\KasusPenyakit;

class KesehatanHandler extends BaseModuleHandler
{
    public function key(): string
    {
        return 'kesehatan';
    }
    public function label(): string
    {
        return 'Kesehatan';
    }
    public function periodType(): string
    {
        return self::MONTHLY;
    }
    public function entityType(): string
    {
        return 'kesehatan'; // Using string identifier since DataKesehatanBulanan table removed
    }
    public function table(): string
    {
        return 'kesehatan_modular';
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
        // Return query for modular health tables
        return IbuHamil::query()
            ->where('kecamatan_id', $kecamatanId)
            ->when($desaId !== null, fn ($query) => $query->where('desa_id', $desaId));
    }

    public function snapshotRows($records, array $context = []): array
    {
        return $records->map(function ($record): array {
            return [
                'entity_id' => $record->getKey(),
                'attributes' => $record->getAttributes(),
            ];
        })->values()->all();
    }
}
