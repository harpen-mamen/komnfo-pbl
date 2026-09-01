<?php

namespace App\Filament\Resources\OpdResource\Pages;

use App\Filament\Resources\OpdResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOpd extends ViewRecord
{
    protected static string $resource = OpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
