<?php

namespace App\Filament\Resources\KeluargaResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\KeluargaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKeluargas extends ListRecords
{
    protected static string $resource = KeluargaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah KK')];
    }
}
