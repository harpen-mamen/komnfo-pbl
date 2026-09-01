<?php

namespace App\Filament\Resources\StokObatResource\Pages;

use App\Filament\Resources\StokObatResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStokObat extends ViewRecord
{
    protected static string $resource = StokObatResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
