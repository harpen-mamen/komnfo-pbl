<?php

namespace App\Services\Submission\Modules;

use Illuminate\Database\Eloquent\Builder;
use App\Models\DataSiswa;
use App\Models\DataGuru;

class PendidikanHandler extends BaseModuleHandler
{
    public function key(): string
    {
        return 'pendidikan';
    }
    public function label(): string
    {
        return 'Pendidikan';
    }
    public function periodType(): string
    {
        return self::SEMESTER;
    }
    public function entityType(): string
    {
        return 'pendidikan'; // Using string identifier since DataPendidikanBulanan table removed
    }
    public function table(): string
    {
        return 'data_pendidikan_modular';
    }

    public function context(?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        return [
            'periode_bulan' => null,
            'semester' => $semester ?? (now()->month <= 6 ? 'ganjil' : 'genap'),
            'periode_tahun' => $periodeTahun ?? now()->year,
        ];
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): Builder
    {
        // Return query for modular education tables
        return DataSiswa::query()
            ->whereHas('sekolah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId));
    }
}
