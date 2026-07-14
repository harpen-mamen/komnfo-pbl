<?php

namespace App\Filament\Resources\DataPendidikanBulananResource\Pages;

use App\Filament\Resources\DataPendidikanBulananResource;
use App\Models\Desa;
use App\Models\PeriodePelaporan;
use App\Support\PanelAccess;
use Filament\Resources\Pages\CreateRecord;

class CreateDataPendidikanBulanan extends CreateRecord
{
    protected static string $resource = DataPendidikanBulananResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Map tahun_ajaran + semester -> periode_pelaporan
        $tahunAjaran = $data['tahun_ajaran'] ?? null;
        $semester = strtolower($data['semester'] ?? 'ganjil');

        if (! $tahunAjaran) {
            abort(400, 'Tahun Ajaran diperlukan.');
        }

        [$start, $end] = explode('/', $tahunAjaran) + [null, null];
        $startYear = (int) $start;
        $endYear = (int) $end;

        if ($semester === 'ganjil') {
            $bulan = 7;
            $tahun = $startYear;
            $tanggalMulai = sprintf('%04d-07-01', $startYear);
            $tanggalSelesai = sprintf('%04d-12-31', $startYear);
        } else {
            $bulan = 1;
            $tahun = $endYear;
            $tanggalMulai = sprintf('%04d-01-01', $endYear);
            $tanggalSelesai = sprintf('%04d-06-30', $endYear);
        }

        $periode = PeriodePelaporan::query()
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('nama_periode', 'like', "%{$tahunAjaran}%")
            ->first();

        if (! $periode) {
            $periode = PeriodePelaporan::create([
                'nama_periode' => trim($tahunAjaran . ' - ' . ucfirst($semester)),
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'status' => 'Draft',
            ]);
        }

        if (PanelAccess::isAdminKecamatan()) {
            abort_unless($periode->isOpenForInput(), 403);
        }

        $data['periode_pelaporan_id'] = $periode->id;
        $data['periode_bulan'] = $periode->bulan;
        $data['periode_tahun'] = $periode->tahun;
        // remove temporary inputs so they won't be saved to model
        unset($data['tahun_ajaran'], $data['semester']);

        if (PanelAccess::isAdminKecamatan()) {
            $data['kecamatan_id'] = PanelAccess::user()?->kecamatan_id;
            $data['status'] = 'Terkirim';
        }

        abort_unless(Desa::query()->whereKey($data['desa_id'])->where('kecamatan_id', $data['kecamatan_id'])->exists(), 403);

        return $data;
    }
}
