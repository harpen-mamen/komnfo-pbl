<?php

namespace App\Filament\Resources\DataGuruResource\Pages;

use App\Filament\Resources\DataGuruResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataGuru extends EditRecord
{
    protected static string $resource = DataGuruResource::class;

    protected function getActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }
}
