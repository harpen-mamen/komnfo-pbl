<?php

namespace App\Policies;

use App\Models\Keluarga;
use App\Models\User;

class KeluargaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasAnyRole(['admin', 'super_admin', 'admin_kominfo']) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin_kecamatan') && $user->kecamatan_id !== null
            || $user->hasRole('admin_desa') && $user->desa_id !== null;
    }

    public function view(User $user, Keluarga $keluarga): bool
    {
        return $this->belongsToUserKecamatan($user, $keluarga);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin_kecamatan') && $user->kecamatan_id !== null
            || $user->hasRole('admin_desa') && $user->desa_id !== null;
    }

    public function update(User $user, Keluarga $keluarga): bool
    {
        return $this->belongsToUserKecamatan($user, $keluarga);
    }

    public function delete(User $user, Keluarga $keluarga): bool
    {
        return $this->belongsToUserKecamatan($user, $keluarga);
    }

    private function belongsToUserKecamatan(User $user, Keluarga $keluarga): bool
    {
        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null && (int) $keluarga->rumah?->desa_id === (int) $user->desa_id;
        }

        return $user->hasRole('admin_kecamatan')
            && $user->kecamatan_id !== null
            && (int) $keluarga->rumah?->desa?->kecamatan_id === (int) $user->kecamatan_id;
    }
}
