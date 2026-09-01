<?php

namespace App\Filament\Resources\DataPrasaranaResource\Pages;

use App\Filament\Resources\DataPrasaranaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataPrasaranas extends ListRecords
{
    protected static string $resource = DataPrasaranaResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
