<?php

namespace App\Filament\Resources\StokObatResource\Pages;

use App\Filament\Resources\StokObatResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStokObats extends ListRecords
{
    protected static string $resource = StokObatResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
