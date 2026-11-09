<?php

namespace App\Filament\Resources\TentangSejarahResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\TentangSejarahResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTentangSejarahs extends ListRecords
{
    protected static string $resource = TentangSejarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Peristiwa'),
        ];
    }
}
