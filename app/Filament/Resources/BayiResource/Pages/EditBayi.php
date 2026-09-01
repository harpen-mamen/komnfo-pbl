<?php

namespace App\Filament\Resources\BayiResource\Pages;

use App\Filament\Resources\BayiResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBayi extends EditRecord
{
    protected static string $resource = BayiResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
