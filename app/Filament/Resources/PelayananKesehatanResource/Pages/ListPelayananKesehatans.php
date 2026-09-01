<?php

namespace App\Filament\Resources\PelayananKesehatanResource\Pages;

use App\Filament\Resources\PelayananKesehatanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelayananKesehatans extends ListRecords
{
    protected static string $resource = PelayananKesehatanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
