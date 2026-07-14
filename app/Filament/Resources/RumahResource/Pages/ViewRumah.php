<?php

namespace App\Filament\Resources\RumahResource\Pages;

use App\Filament\Resources\RumahResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRumah extends ViewRecord
{
    protected static string $resource = RumahResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
