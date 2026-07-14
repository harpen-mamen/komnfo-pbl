<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private ?string $role = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->role = $data['role'] ?? null;
        unset($data['role']);

        if (! in_array($this->role, ['admin_kecamatan', 'admin_desa'], true)) {
            $data['kecamatan_id'] = null;
        }

        if ($this->role !== 'admin_desa') {
            $data['desa_id'] = null;
        }

        if ($this->role !== 'admin_opd') {
            $data['opd_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->role) {
            $this->record->syncRoles([$this->role]);
        }
    }
}
