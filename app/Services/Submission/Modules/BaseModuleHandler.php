<?php

namespace App\Services\Submission\Modules;

use Illuminate\Database\Eloquent\Builder;

/**
 * Base class for all module handlers.
 *
 * Handlers implement module-specific:
 *  - context (period interpretation)
 *  - query (which records to include)
 *  - snapshotRows (what to save in payload)
 */
abstract class BaseModuleHandler
{
    public const MASTER = 'master';    // Master data, no period
    public const MONTHLY = 'monthly';  // Monthly period (bulanan)
    public const SEMESTER = 'semester'; // Semester period

    abstract public function key(): string;
    abstract public function label(): string;
    abstract public function periodType(): string;
    abstract public function entityType(): ?string;
    abstract public function table(): string;

    public function context(?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        return [
            'periode_bulan' => $periodeBulan,
            'semester' => $semester,
            'periode_tahun' => $periodeTahun,
        ];
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): Builder
    {
        return $this->baseQuery()->where('kecamatan_id', $kecamatanId);
    }

    public function snapshotRows($records, array $context = []): array
    {
        return $records->map(fn ($r) => [
            'entity_id' => $r->getKey(),
            'attributes' => $r->getAttributes(),
        ])->values()->all();
    }

    public function baseQuery(): Builder
    {
        $model = $this->entityType();
        return $model ? $model::query() : null;
    }
}
