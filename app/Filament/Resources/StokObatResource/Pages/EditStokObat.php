<?php

namespace App\Filament\Resources\StokObatResource\Pages;

use App\Filament\Resources\StokObatResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStokObat extends EditRecord
{
    protected static string $resource = StokObatResource::class;

    protected function getActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        if (!isset($data['stok_akhir'])) {
            $data['stok_akhir'] = ($data['stok_awal'] ?? 0) + ($data['terima_dari_gudang'] ?? 0) - ($data['jumlah_dipakai'] ?? 0);
        }
        return $data;
    }
}
