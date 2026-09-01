<?php

namespace App\Filament\Resources\PejabatProfileResource\Pages;

use App\Filament\Resources\PejabatProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPejabatProfile extends EditRecord
{
    protected static string $resource = PejabatProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
