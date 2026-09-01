<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Illuminate\Auth\Access\AuthorizationException;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    private ?string $role = null;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->role = $data['role'] ?? null;
        unset($data['role']);

        if (! UserResource::canEdit($this->record) || ! UserResource::canAssignRole($this->role)) {
            throw new AuthorizationException('Akun atau role pengguna tidak dapat diubah oleh akun Anda.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
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

    protected function afterSave(): void
    {
        if ($this->role) {
            $this->record->syncRoles([$this->role]);
        }
    }
}
