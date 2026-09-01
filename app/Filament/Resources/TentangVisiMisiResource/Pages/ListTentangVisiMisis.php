<?php

namespace App\Filament\Resources\TentangVisiMisiResource\Pages;

use App\Filament\Resources\TentangVisiMisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTentangVisiMisis extends ListRecords
{
    protected static string $resource = TentangVisiMisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Item'),
        ];
    }
}
