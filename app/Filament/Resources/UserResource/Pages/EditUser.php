<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
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

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

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

    protected function afterSave(): void
    {
        if ($this->role) {
            $this->record->syncRoles([$this->role]);
        }
    }
}
