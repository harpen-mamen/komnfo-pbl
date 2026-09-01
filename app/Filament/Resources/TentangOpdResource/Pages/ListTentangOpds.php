<?php

namespace App\Filament\Resources\TentangOpdResource\Pages;

use App\Filament\Resources\TentangOpdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTentangOpds extends ListRecords
{
    protected static string $resource = TentangOpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah OPD'),
        ];
    }
}
