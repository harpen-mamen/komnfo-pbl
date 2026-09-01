<?php

namespace App\Filament\Resources\PejabatProfileResource\Pages;

use App\Filament\Resources\PejabatProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPejabatProfiles extends ListRecords
{
    protected static string $resource = PejabatProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Pejabat'),
        ];
    }
}
