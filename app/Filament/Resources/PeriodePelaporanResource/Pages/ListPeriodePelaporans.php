<?php

namespace App\Filament\Resources\PeriodePelaporanResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\PeriodePelaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodePelaporans extends ListRecords
{
    protected static string $resource = PeriodePelaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
