<?php

namespace App\Filament\Resources\DataKesehatanBulananResource\Pages;

use App\Filament\Resources\DataKesehatanBulananResource;
use App\Models\Desa;
use App\Models\PeriodePelaporan;
use App\Support\PanelAccess;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataKesehatanBulanan extends EditRecord
{
    protected static string $resource = DataKesehatanBulananResource::class;

    protected function getActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $periode = PeriodePelaporan::query()->findOrFail($data['periode_pelaporan_id']);

        if (PanelAccess::isAdminKecamatan()) {
            abort_unless($periode->isOpenForInput(), 403);
        }

        $data['periode_bulan'] = $periode->bulan;
        $data['periode_tahun'] = $periode->tahun;

        if (PanelAccess::isAdminKecamatan()) {
            $data['kecamatan_id'] = PanelAccess::user()?->kecamatan_id;
            $data['status'] = $this->record->status ?: 'Terkirim';
        }

        return $data;
    }
}
