<?php

namespace App\Filament\Resources\DataSiswaResource\Pages;

use App\Filament\Resources\DataSiswaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataSiswa extends EditRecord
{
    protected static string $resource = DataSiswaResource::class;

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
