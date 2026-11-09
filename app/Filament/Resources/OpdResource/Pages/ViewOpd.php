<?php

namespace App\Filament\Resources\OpdResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\OpdResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOpd extends ViewRecord
{
    protected static string $resource = OpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
