<?php

namespace App\Filament\Resources\DataPendidikanBulananResource\Pages;

use App\Filament\Resources\DataPendidikanBulananResource;
use App\Models\Desa;
use App\Models\PeriodePelaporan;
use App\Support\PanelAccess;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPendidikanBulanan extends EditRecord
{
    protected static string $resource = DataPendidikanBulananResource::class;

    protected function getActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Map tahun_ajaran + semester -> periode_pelaporan
        $tahunAjaran = $data['tahun_ajaran'] ?? null;
        $semester = strtolower($data['semester'] ?? 'ganjil');

        if ($tahunAjaran) {
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
            unset($data['tahun_ajaran'], $data['semester']);
        } else {
            // fallback to existing behavior if no tahun_ajaran provided
            $periode = PeriodePelaporan::query()->findOrFail($data['periode_pelaporan_id']);

            if (PanelAccess::isAdminKecamatan()) {
                abort_unless($periode->isOpenForInput(), 403);
            }

            $data['periode_bulan'] = $periode->bulan;
            $data['periode_tahun'] = $periode->tahun;
        }

        if (PanelAccess::isAdminKecamatan()) {
            $data['kecamatan_id'] = PanelAccess::user()?->kecamatan_id;
            $data['status'] = $this->record->status ?: 'Terkirim';
        }

        abort_unless(Desa::query()->whereKey($data['desa_id'])->where('kecamatan_id', $data['kecamatan_id'])->exists(), 403);

        return $data;
    }
}
