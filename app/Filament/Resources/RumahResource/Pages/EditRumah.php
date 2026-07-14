<?php

namespace App\Filament\Resources\RumahResource\Pages;

use App\Filament\Resources\RumahResource;
use App\Support\AdminKecamatan;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRumah extends EditRecord
{
    protected static string $resource = RumahResource::class;

    protected function getActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(AdminKecamatan::canUseDesa((int) ($data['desa_id'] ?? 0)), 403);

        return $data;
    }
}
