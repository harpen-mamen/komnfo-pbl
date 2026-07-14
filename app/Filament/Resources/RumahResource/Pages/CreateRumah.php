<?php

namespace App\Filament\Resources\RumahResource\Pages;

use App\Filament\Resources\RumahResource;
use App\Support\AdminKecamatan;
use Filament\Resources\Pages\CreateRecord;

class CreateRumah extends CreateRecord
{
    protected static string $resource = RumahResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        abort_unless(AdminKecamatan::canUseDesa((int) ($data['desa_id'] ?? 0)), 403);

        return $data;
    }
}
