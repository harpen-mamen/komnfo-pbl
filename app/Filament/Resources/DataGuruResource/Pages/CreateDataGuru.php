<?php

namespace App\Filament\Resources\DataGuruResource\Pages;

use App\Filament\Resources\DataGuruResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDataGuru extends CreateRecord
{
    protected static string $resource = DataGuruResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        return $data;
    }
}
