<?php

namespace App\Filament\Resources\TentangOpdResource\Pages;

use App\Filament\Resources\TentangOpdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTentangOpd extends EditRecord
{
    protected static string $resource = TentangOpdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
