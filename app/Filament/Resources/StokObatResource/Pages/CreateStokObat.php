<?php

namespace App\Filament\Resources\StokObatResource\Pages;

use App\Filament\Resources\StokObatResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStokObat extends CreateRecord
{
    protected static string $resource = StokObatResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        // Auto-calculate stok_akhir
        if (!isset($data['stok_akhir'])) {
            $data['stok_akhir'] = ($data['stok_awal'] ?? 0) + ($data['terima_dari_gudang'] ?? 0) - ($data['jumlah_dipakai'] ?? 0);
        }
        return $data;
    }
}
