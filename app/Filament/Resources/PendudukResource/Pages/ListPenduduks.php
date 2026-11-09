<?php

namespace App\Filament\Resources\PendudukResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PendudukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenduduks extends ListRecords
{
    protected static string $resource = PendudukResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Penduduk')];
    }
}
