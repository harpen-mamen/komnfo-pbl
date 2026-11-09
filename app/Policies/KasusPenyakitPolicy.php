<?php

namespace App\Policies;

use App\Models\KasusPenyakit;
use App\Models\User;
use App\Support\AppRole;

/**
 * A14: Authorization policy for KasusPenyakit.
 */
class KasusPenyakitPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasAnyRole([AppRole::ADMIN->value, AppRole::SUPER_ADMIN->value, AppRole::ADMIN_KOMINFO->value])
            ? true
            : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(AppRole::ADMIN_KECAMATAN->value) && $user->kecamatan_id !== null
            || $this->isKesehatanOpd($user);
    }

    public function view(User $user, KasusPenyakit $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isKesehatanOpd($user)) {
            return (int) $record->created_by === (int) $user->id
                || ($user->puskes_id
                    && (int) $record->kecamatan_id === (int) optional($user->puskes)->kecamatan_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isKesehatanOpd($user);
    }

    public function update(User $user, KasusPenyakit $record): bool
    {
        return $this->view($user, $record);
    }

    public function delete(User $user, KasusPenyakit $record): bool
    {
        return $this->view($user, $record);
    }

    private function isKesehatanOpd(User $user): bool
    {
        return $user->hasAnyRole([
            AppRole::ADMIN_DINKES->value,
            AppRole::OPERATOR_DINKES->value,
            
            
            
            
        ]);
    }
}
