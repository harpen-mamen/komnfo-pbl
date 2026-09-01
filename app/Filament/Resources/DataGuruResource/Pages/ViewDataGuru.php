<?php

namespace App\Filament\Resources\DataGuruResource\Pages;

use App\Filament\Resources\DataGuruResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataGuru extends ViewRecord
{
    protected static string $resource = DataGuruResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
