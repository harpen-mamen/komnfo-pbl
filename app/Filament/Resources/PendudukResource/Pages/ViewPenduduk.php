<?php

namespace App\Filament\Resources\PendudukResource\Pages;

use App\Filament\Resources\PendudukResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenduduk extends ViewRecord
{
    protected static string $resource = PendudukResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
