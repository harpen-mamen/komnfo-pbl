<?php

namespace App\Filament\Resources\PendudukResource\Pages;

use App\Filament\Resources\PendudukResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenduduks extends ListRecords
{
    protected static string $resource = PendudukResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()->label('Tambah Penduduk')];
    }
}
