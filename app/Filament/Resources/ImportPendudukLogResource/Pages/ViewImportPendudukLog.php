<?php

namespace App\Filament\Resources\ImportPendudukLogResource\Pages;

use App\Filament\Resources\ImportPendudukLogResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewImportPendudukLog extends ViewRecord
{
    protected static string $resource = ImportPendudukLogResource::class;

    protected function getActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
