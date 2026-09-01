<?php

namespace App\Services\Submission\Modules;

use App\Models\DataGuru;
use App\Models\DataPrasarana;
use App\Models\DataSiswa;
use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handler for sekolah/education data modules.
 *
 * Covers:
 *  - Data Guru (teacher data)
 *  - Data Siswa (student data)
 *  - Data Prasarana (infrastructure data)
 */
class SekolahHandler extends BaseModuleHandler
{
    public function key(): string { return 'sekolah'; }
    public function label(): string { return 'Pendidikan Sekolah'; }
    public function periodType(): string { return self::SEMESTER; }
    public function entityType(): string { return Sekolah::class; }
    public function table(): string { return 'sekolahs'; }

    public function context(?int $periodeBulan, ?string $semester, ?int $periodeTahun): array
    {
        $tahunAjaran = $periodeTahun
            ? "{$periodeTahun}/" . ($periodeTahun + 1)
            : now()->year . '/' . (now()->year + 1);

        return [
            'periode_bulan' => null,
            'semester' => $semester ?? (now()->month <= 6 ? 'ganjil' : 'genap'),
            'periode_tahun' => $periodeTahun ?? now()->year,
            'tahun_ajaran' => $tahunAjaran,
        ];
    }

    public function query(int $kecamatanId, array $context, ?int $desaId): Builder
    {
        return Sekolah::query()
            ->where('kecamatan_id', $kecamatanId);
    }

    /**
     * Snapshot for sekolah includes school info + related child records.
     * Uses $context instead of request() for CLI/API/Queue compatibility.
     */
    public function snapshotRows($records, array $context = []): array
    {
        $tahunAjaran = $context['tahun_ajaran'] ?? (now()->year . '/' . (now()->year + 1));
        $semester = $context['semester'] ?? (now()->month <= 6 ? 'ganjil' : 'genap');

        return $records->map(function (Sekolah $sekolah) use ($tahunAjaran, $semester) {
            return [
                'entity_id' => $sekolah->getKey(),
                'attributes' => $sekolah->getAttributes(),
                'sekolah' => [
                    'nama_sekolah' => $sekolah->nama_sekolah,
                    'npsn' => $sekolah->npsn,
                    'jenjang' => $sekolah->jenjang,
                    'status' => $sekolah->status,
                    'alamat' => $sekolah->alamat,
                    'kepala_sekolah' => $sekolah->kepala_sekolah,
                    'akreditasi' => $sekolah->akreditasi,
                ],
                'data_guru' => DataGuru::where('sekolah_id', $sekolah->id)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->get()
                    ->map(fn ($r) => $r->getAttributes())->values()->all(),
                'data_siswa' => DataSiswa::where('sekolah_id', $sekolah->id)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->get()
                    ->map(fn ($r) => $r->getAttributes())->values()->all(),
                'data_prasaranas' => DataPrasarana::where('sekolah_id', $sekolah->id)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->get()
                    ->map(fn ($r) => $r->getAttributes())->values()->all(),
            ];
        })->values()->all();
    }
}
