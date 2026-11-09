<?php

namespace App\Services\Submission\Modules;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Penduduk;

class PendudukHandler extends BaseModuleHandler
{
    public function key(): string
    {
        return 'penduduk';
    }
    public function label(): string
    {
        return 'Penduduk';
    }
    public function periodType(): string
    {
        return self::MASTER;
    }
    public function entityType(): string
    {
        return Penduduk::class;
    }
    public function table(): string
    {
        return 'penduduks';
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): Builder
    {
        return Penduduk::query()->whereHas(
            'keluarga.rumah.desa',
            fn ($query) => $query->where('kecamatan_id', $kecamatanId)
                ->when($desaId !== null, fn ($query) => $query->whereKey($desaId))
        );
    }
}
