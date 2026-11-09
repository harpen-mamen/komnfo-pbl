<?php

namespace App\Filament\Resources\PeriodePelaporanResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\PeriodePelaporanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPeriodePelaporan extends ViewRecord
{
    protected static string $resource = PeriodePelaporanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
