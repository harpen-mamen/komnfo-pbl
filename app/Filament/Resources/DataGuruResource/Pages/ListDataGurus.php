<?php

namespace App\Filament\Resources\DataGuruResource\Pages;

use App\Filament\Resources\DataGuruResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataGurus extends ListRecords
{
    protected static string $resource = DataGuruResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
