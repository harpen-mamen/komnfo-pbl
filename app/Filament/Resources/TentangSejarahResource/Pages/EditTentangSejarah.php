<?php

namespace App\Filament\Resources\TentangSejarahResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\TentangSejarahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTentangSejarah extends EditRecord
{
    protected static string $resource = TentangSejarahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
