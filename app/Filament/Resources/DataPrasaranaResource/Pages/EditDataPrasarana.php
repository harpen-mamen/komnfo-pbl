<?php

namespace App\Filament\Resources\DataPrasaranaResource\Pages;

use App\Filament\Resources\DataPrasaranaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPrasarana extends EditRecord
{
    protected static string $resource = DataPrasaranaResource::class;

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
