<?php

namespace App\Filament\Resources\DataKesehatanBulananResource\Pages;

use App\Filament\Resources\DataKesehatanBulananResource;
use App\Models\Desa;
use App\Models\PeriodePelaporan;
use App\Support\PanelAccess;
use Filament\Resources\Pages\CreateRecord;

class CreateDataKesehatanBulanan extends CreateRecord
{
    protected static string $resource = DataKesehatanBulananResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $periode = PeriodePelaporan::query()->findOrFail($data['periode_pelaporan_id']);

        if (PanelAccess::isAdminKecamatan()) {
            abort_unless($periode->isOpenForInput(), 403);
        }

        $data['periode_bulan'] = $periode->bulan;
        $data['periode_tahun'] = $periode->tahun;

        if (PanelAccess::isAdminKecamatan()) {
            $data['kecamatan_id'] = PanelAccess::user()?->kecamatan_id;
            $data['status'] = 'Terkirim';
        }

        abort_unless(Desa::query()->whereKey($data['desa_id'])->where('kecamatan_id', $data['kecamatan_id'])->exists(), 403);

        return $data;
    }
}
