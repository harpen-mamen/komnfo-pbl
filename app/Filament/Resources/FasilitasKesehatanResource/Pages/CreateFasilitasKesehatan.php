<?php

namespace App\Filament\Resources\FasilitasKesehatanResource\Pages;

use App\Filament\Resources\FasilitasKesehatanResource;
use App\Models\FasilitasKesehatan;
use App\Models\Kecamatan;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateFasilitasKesehatan extends CreateRecord
{
    protected static string $resource = FasilitasKesehatanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['kode_faskes']) && !empty($data['kecamatan_id'])) {
            $kecamatan = Kecamatan::find($data['kecamatan_id']);
            $jenis = $data['jenis'] ?? 'Puskesmas';
            $data['kode_faskes'] = FasilitasKesehatan::generateKodeFaskes($kecamatan, $jenis);
        }
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        return $data;
    }
}
