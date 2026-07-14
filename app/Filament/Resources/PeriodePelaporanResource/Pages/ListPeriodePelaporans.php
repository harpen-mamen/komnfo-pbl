<?php

namespace App\Filament\Resources\PeriodePelaporanResource\Pages;

use App\Filament\Resources\PeriodePelaporanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodePelaporans extends ListRecords
{
    protected static string $resource = PeriodePelaporanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
