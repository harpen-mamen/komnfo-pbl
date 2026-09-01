<?php

namespace App\Filament\Resources\DataSiswaResource\Pages;

use App\Filament\Resources\DataSiswaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataSiswa extends ViewRecord
{
    protected static string $resource = DataSiswaResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
