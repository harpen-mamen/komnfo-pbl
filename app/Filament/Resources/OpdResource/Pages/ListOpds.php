<?php

namespace App\Filament\Resources\OpdResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\OpdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpds extends ListRecords
{
    protected static string $resource = OpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
