<?php

namespace App\Filament\Resources\DataPrasaranaResource\Pages;

use App\Filament\Resources\DataPrasaranaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDataPrasarana extends CreateRecord
{
    protected static string $resource = DataPrasaranaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        return $data;
    }
}
