<?php

namespace App\Filament\Resources\DataPendidikanBulananResource\Pages;

use App\Filament\Resources\DataPendidikanBulananResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataPendidikanBulanan extends ViewRecord
{
    protected static string $resource = DataPendidikanBulananResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
