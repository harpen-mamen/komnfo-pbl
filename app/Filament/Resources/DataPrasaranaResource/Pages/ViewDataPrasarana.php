<?php

namespace App\Filament\Resources\DataPrasaranaResource\Pages;

use App\Filament\Resources\DataPrasaranaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataPrasarana extends ViewRecord
{
    protected static string $resource = DataPrasaranaResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
