<?php

namespace App\Filament\Resources\AboutSectionContentResource\Pages;

use App\Filament\Resources\AboutSectionContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAboutSectionContent extends CreateRecord
{
    protected static string $resource = AboutSectionContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        return $data;
    }
}
