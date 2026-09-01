<?php

namespace App\Filament\Resources\TenagaKesehatanResource\Pages;

use App\Filament\Resources\TenagaKesehatanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenagaKesehatans extends ListRecords
{
    protected static string $resource = TenagaKesehatanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
