<?php

namespace App\Filament\Resources\RekamMedisResource\Pages;

use App\Filament\Resources\RekamMedisResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRekamMedis extends EditRecord
{
    protected static string $resource = RekamMedisResource::class;

    protected function getActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
