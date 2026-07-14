<?php

namespace App\Filament\Resources\PendudukResource\Pages;

use App\Filament\Resources\PendudukResource;
use App\Models\Keluarga;
use Filament\Resources\Pages\CreateRecord;

class CreatePenduduk extends CreateRecord
{
    protected static string $resource = PendudukResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        abort_unless(Keluarga::query()->visibleToCurrentUser()->whereKey($data['keluarga_id'])->exists(), 403);

        return $data;
    }

    protected function afterCreate(): void
    {
        Keluarga::query()->whereKey($this->record->keluarga_id)->increment('jumlah_anggota');
    }
}
