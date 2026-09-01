<?php

namespace App\Filament\Resources\KasusPenyakitResource\Pages;

use App\Filament\Resources\KasusPenyakitResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\Actions;

class EditKasusPenyakit extends EditRecord
{
    protected static string $resource = KasusPenyakitResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
