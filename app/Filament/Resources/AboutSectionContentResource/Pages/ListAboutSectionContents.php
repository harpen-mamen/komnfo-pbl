<?php

namespace App\Filament\Resources\AboutSectionContentResource\Pages;

use App\Filament\Resources\AboutSectionContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAboutSectionContents extends ListRecords
{
    protected static string $resource = AboutSectionContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Section'),
        ];
    }
}
