<?php

namespace App\Filament\Resources\RekamMedisResource\Pages;

use App\Filament\Resources\RekamMedisResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRekamMedis extends CreateRecord
{
    protected static string $resource = RekamMedisResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        return $data;
    }
}
