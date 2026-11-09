<?php

namespace App\Filament\Resources\PendudukResource\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\PendudukResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenduduk extends ViewRecord
{
    protected static string $resource = PendudukResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
