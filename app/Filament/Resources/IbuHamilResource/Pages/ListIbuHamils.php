<?php

namespace App\Filament\Resources\IbuHamilResource\Pages;

use App\Filament\Resources\IbuHamilResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIbuHamils extends ListRecords
{
    protected static string $resource = IbuHamilResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
