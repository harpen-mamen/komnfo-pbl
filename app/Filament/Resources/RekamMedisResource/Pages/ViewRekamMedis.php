<?php

namespace App\Filament\Resources\RekamMedisResource\Pages;

use App\Filament\Resources\RekamMedisResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRekamMedis extends ViewRecord
{
    protected static string $resource = RekamMedisResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
