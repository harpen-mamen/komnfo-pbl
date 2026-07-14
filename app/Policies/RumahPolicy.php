<?php

namespace App\Policies;

use App\Models\Rumah;
use App\Models\User;

class RumahPolicy
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

    public function view(User $user, Rumah $rumah): bool
    {
        return $this->belongsToUserKecamatan($user, $rumah);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin_kecamatan') && $user->kecamatan_id !== null
            || $user->hasRole('admin_desa') && $user->desa_id !== null;
    }

    public function update(User $user, Rumah $rumah): bool
    {
        return $this->belongsToUserKecamatan($user, $rumah);
    }

    public function delete(User $user, Rumah $rumah): bool
    {
        return $this->belongsToUserKecamatan($user, $rumah);
    }

    private function belongsToUserKecamatan(User $user, Rumah $rumah): bool
    {
        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null && (int) $rumah->desa_id === (int) $user->desa_id;
        }

        return $user->hasRole('admin_kecamatan')
            && $user->kecamatan_id !== null
            && (int) $rumah->desa?->kecamatan_id === (int) $user->kecamatan_id;
    }
}
