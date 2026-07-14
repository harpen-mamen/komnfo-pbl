<?php

namespace App\Filament\Resources\RumahResource\Pages;

use App\Filament\Resources\RumahResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRumah extends ListRecords
{
    protected static string $resource = RumahResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()->label('Tambah Rumah')];
    }
}
