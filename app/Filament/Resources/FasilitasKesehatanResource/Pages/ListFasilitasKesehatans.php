<?php

namespace App\Filament\Resources\FasilitasKesehatanResource\Pages;

use App\Filament\Resources\FasilitasKesehatanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFasilitasKesehatans extends ListRecords
{
    protected static string $resource = FasilitasKesehatanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
