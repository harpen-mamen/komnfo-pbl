<?php

namespace App\Filament\Resources\PendudukResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\PendudukResource;
use App\Models\Keluarga;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenduduk extends EditRecord
{
    protected static string $resource = PendudukResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(Keluarga::query()->visibleToCurrentUser()->whereKey($data['keluarga_id'])->exists(), 403);

        return $data;
    }
}
