<?php

namespace App\Policies;

use App\Models\ImportPendudukLog;
use App\Models\User;

class ImportPendudukLogPolicy
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

    public function view(User $user, ImportPendudukLog $log): bool
    {
        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null && (int) $log->desa_id === (int) $user->desa_id;
        }

        return $user->hasRole('admin_kecamatan')
            && $user->kecamatan_id !== null
            && ((int) $log->kecamatan_id === (int) $user->kecamatan_id
                || (int) $log->desa?->kecamatan_id === (int) $user->kecamatan_id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ImportPendudukLog $log): bool
    {
        return false;
    }

    public function delete(User $user, ImportPendudukLog $log): bool
    {
        return $this->view($user, $log);
    }
}
