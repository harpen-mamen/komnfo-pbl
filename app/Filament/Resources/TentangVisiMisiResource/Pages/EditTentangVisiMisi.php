<?php

namespace App\Filament\Resources\TentangVisiMisiResource\Pages;

use App\Filament\Resources\TentangVisiMisiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTentangVisiMisi extends EditRecord
{
    protected static string $resource = TentangVisiMisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
