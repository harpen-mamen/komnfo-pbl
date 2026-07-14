<?php

namespace App\Filament\Resources\PeriodePelaporanResource\Pages;

use App\Filament\Resources\PeriodePelaporanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPeriodePelaporan extends ViewRecord
{
    protected static string $resource = PeriodePelaporanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
