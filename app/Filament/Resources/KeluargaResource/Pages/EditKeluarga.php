<?php

namespace App\Filament\Resources\KeluargaResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\KeluargaResource;
use App\Models\Rumah;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKeluarga extends EditRecord
{
    protected static string $resource = KeluargaResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(Rumah::query()->visibleToCurrentUser()->whereKey($data['rumah_id'])->exists(), 403);

        return $data;
    }
}
