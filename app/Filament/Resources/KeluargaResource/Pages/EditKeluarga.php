<?php

namespace App\Filament\Resources\KeluargaResource\Pages;

use App\Filament\Resources\KeluargaResource;
use App\Models\Rumah;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKeluarga extends EditRecord
{
    protected static string $resource = KeluargaResource::class;

    protected function getActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(Rumah::query()->visibleToCurrentUser()->whereKey($data['rumah_id'])->exists(), 403);

        return $data;
    }
}
