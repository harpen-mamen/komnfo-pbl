<?php

namespace App\Filament\Resources\KasusPenyakitResource\Pages;

use App\Filament\Resources\KasusPenyakitResource;
use App\Support\PanelAccess;
use Filament\Resources\Pages\CreateRecord;

class CreateKasusPenyakit extends CreateRecord
{
    protected static string $resource = KasusPenyakitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (PanelAccess::isAdminKecamatan()) {
            $data['kecamatan_id'] = PanelAccess::user()?->kecamatan_id;
        }
        $data['created_by'] = auth()->id();
        return $data;
    }
}
