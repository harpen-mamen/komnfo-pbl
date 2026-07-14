<?php

namespace App\Filament\Resources\PendudukResource\Pages;

use App\Filament\Resources\PendudukResource;
use App\Models\Keluarga;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenduduk extends EditRecord
{
    protected static string $resource = PendudukResource::class;

    protected function getActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(Keluarga::query()->visibleToCurrentUser()->whereKey($data['keluarga_id'])->exists(), 403);

        return $data;
    }
}
