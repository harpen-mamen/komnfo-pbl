<?php

namespace App\Filament\Resources\SekolahResource\Pages;

use App\Filament\Resources\SekolahResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSekolah extends CreateRecord
{
    protected static string $resource = SekolahResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        return $data;
    }
}
