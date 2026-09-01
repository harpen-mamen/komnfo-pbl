<?php

namespace App\Filament\Resources\TenagaKesehatanResource\Pages;

use App\Filament\Resources\TenagaKesehatanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenagaKesehatan extends EditRecord
{
    protected static string $resource = TenagaKesehatanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
