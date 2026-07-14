<?php

namespace App\Filament\Resources\KeluargaResource\Pages;

use App\Filament\Resources\KeluargaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKeluargas extends ListRecords
{
    protected static string $resource = KeluargaResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()->label('Tambah KK')];
    }
}
