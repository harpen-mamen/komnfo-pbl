<?php

namespace App\Services\Submission\Modules;

use App\Models\Keluarga;

class KeluargaHandler extends BaseModuleHandler
{
    public function key(): string { return 'keluarga'; }
    public function label(): string { return 'Keluarga'; }
    public function periodType(): string { return self::MASTER; }
    public function entityType(): string { return Keluarga::class; }
    public function table(): string { return 'keluargas'; }

    public function query(int $kecamatanId, array $context, ?int $desaId): \Illuminate\Database\Eloquent\Builder
    {
        return Keluarga::query()->whereHas(
            'rumah.desa',
            fn ($query) => $query->where('kecamatan_id', $kecamatanId)
                ->when($desaId !== null, fn ($query) => $query->whereKey($desaId))
        );
    }
}
