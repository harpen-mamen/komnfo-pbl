<?php

namespace App\Filament\Resources\AboutPageSettingResource\Pages;

use App\Filament\Resources\AboutPageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAboutPageSettings extends ListRecords
{
    protected static string $resource = AboutPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
