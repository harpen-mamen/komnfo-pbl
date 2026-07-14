<?php

namespace App\Filament\Resources\DataKesehatanBulananResource\Pages;

use App\Filament\Resources\DataKesehatanBulananResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataKesehatanBulanans extends ListRecords
{
    protected static string $resource = DataKesehatanBulananResource::class;

    protected function getActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
