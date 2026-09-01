<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Access\AuthorizationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    private ?string $role = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->role = $data['role'] ?? null;
        unset($data['role']);

        if (! UserResource::canAssignRole($this->role)) {
            throw new AuthorizationException('Role pengguna tidak dapat ditetapkan oleh akun Anda.');
        }

        // Reset wilayah for non-wilayah roles
        if (! in_array($this->role, ['admin_kecamatan', 'admin_desa'], true)) {
            $data['kecamatan_id'] = null;
        }

        if ($this->role !== 'admin_desa') {
            $data['desa_id'] = null;
        }

        // Reset OPD for non-OPD roles
        if (! UserResource::isOpdRole($this->role)) {
            $data['opd_id'] = null;
        }

        // Reset puskes_id for non-puskes roles
        if (! in_array($this->role, ['admin_puskes', 'operator_puskes'], true)) {
            $data['puskes_id'] = null;
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
