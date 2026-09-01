<?php

namespace App\Filament\Resources\PelayananKesehatanResource\Pages;

use App\Filament\Resources\PelayananKesehatanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPelayananKesehatan extends EditRecord
{
    protected static string $resource = PelayananKesehatanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
