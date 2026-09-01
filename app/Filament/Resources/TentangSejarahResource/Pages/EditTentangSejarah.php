<?php

namespace App\Filament\Resources\TentangSejarahResource\Pages;

use App\Filament\Resources\TentangSejarahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTentangSejarah extends EditRecord
{
    protected static string $resource = TentangSejarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
