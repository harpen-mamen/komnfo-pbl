<?php

namespace App\Filament\Resources\SekolahResource\Pages;

use App\Filament\Resources\SekolahResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSekolah extends ViewRecord
{
    protected static string $resource = SekolahResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
