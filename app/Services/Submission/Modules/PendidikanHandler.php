<?php

namespace App\Services\Submission\Modules;

use App\Models\DataPendidikanBulanan;
use Illuminate\Support\Facades\Schema;

class PendidikanHandler extends BaseModuleHandler
{
    public function key(): string { return 'pendidikan'; }
    public function label(): string { return 'Pendidikan'; }
    public function periodType(): string { return self::SEMESTER; }
    public function entityType(): string { return DataPendidikanBulanan::class; }
    public function table(): string { return 'data_pendidikan_bulanans'; }

    public function context(?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        return [
            'periode_bulan' => null,
            'semester' => $semester ?? (now()->month <= 6 ? 'ganjil' : 'genap'),
            'periode_tahun' => $periodeTahun ?? now()->year,
        ];
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): \Illuminate\Database\Eloquent\Builder
    {
        $query = DataPendidikanBulanan::query()->where('kecamatan_id', $kecamatanId);
        $this->applySemesterFilter($query, $context['semester'] ?? null);
        if (($context['periode_tahun'] ?? null) !== null) {
            $query->where('periode_tahun', $context['periode_tahun']);
        }
        return $query;
    }

    private function applySemesterFilter($query, ?string $semester): void
    {
        if (empty($semester)) {
            return;
        }

        if (Schema::hasColumn($this->table(), 'semester')) {
            $query->where('semester', $semester);
            return;
        }

        if ($semester === 'ganjil') {
            $query->whereBetween('periode_bulan', [1, 6]);
        } elseif ($semester === 'genap') {
            $query->whereBetween('periode_bulan', [7, 12]);
        }
    }
}
