<?php

namespace App\Services\Submission\Modules;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Rumah;

class RumahHandler extends BaseModuleHandler
{
    public function key(): string
    {
        return 'rumah';
    }
    public function label(): string
    {
        return 'Rumah';
    }
    public function periodType(): string
    {
        return self::MASTER;
    }
    public function entityType(): string
    {
        return Rumah::class;
    }
    public function table(): string
    {
        return 'rumahs';
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): Builder
    {
        return Rumah::query()->whereHas(
            'desa',
            fn ($query) => $query->where('kecamatan_id', $kecamatanId)
                ->when($desaId !== null, fn ($query) => $query->whereKey($desaId))
        );
    }
}
