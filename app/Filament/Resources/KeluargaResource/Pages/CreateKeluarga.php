<?php

namespace App\Filament\Resources\KeluargaResource\Pages;

use App\Filament\Resources\KeluargaResource;
use App\Models\Rumah;
use Filament\Resources\Pages\CreateRecord;

class CreateKeluarga extends CreateRecord
{
    protected static string $resource = KeluargaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        abort_unless(Rumah::query()->visibleToCurrentUser()->whereKey($data['rumah_id'])->exists(), 403);

        return $data;
    }
}
