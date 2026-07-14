<?php

namespace App\Policies;

use App\Models\Penduduk;
use App\Models\User;

class PendudukPolicy
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

    public function view(User $user, Penduduk $penduduk): bool
    {
        return $this->belongsToUserKecamatan($user, $penduduk);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin_kecamatan') && $user->kecamatan_id !== null
            || $user->hasRole('admin_desa') && $user->desa_id !== null;
    }

    public function update(User $user, Penduduk $penduduk): bool
    {
        return $this->belongsToUserKecamatan($user, $penduduk);
    }

    public function delete(User $user, Penduduk $penduduk): bool
    {
        return $this->belongsToUserKecamatan($user, $penduduk);
    }

    private function belongsToUserKecamatan(User $user, Penduduk $penduduk): bool
    {
        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null && (int) $penduduk->keluarga?->rumah?->desa_id === (int) $user->desa_id;
        }

        return $user->hasRole('admin_kecamatan')
            && $user->kecamatan_id !== null
            && (int) $penduduk->keluarga?->rumah?->desa?->kecamatan_id === (int) $user->kecamatan_id;
    }
}
