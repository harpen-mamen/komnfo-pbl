<?php

namespace App\Policies;

use App\Models\PelayananKesehatan;
use App\Models\User;
use App\Support\AppRole;

/**
 * A14: Authorization policy for PelayananKesehatan.
 */
class PelayananKesehatanPolicy
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

    public function view(User $user, PelayananKesehatan $record): bool
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

    public function update(User $user, PelayananKesehatan $record): bool
    {
        if (! $this->isKesehatanOpd($user)) {
            return false;
        }

        if ($user->puskes_id) {
            return (int) $record->kecamatan_id === (int) optional($user->puskes)->kecamatan_id;
        }

        return (int) $record->created_by === (int) $user->id;
    }

    public function delete(User $user, PelayananKesehatan $record): bool
    {
        return $this->update($user, $record);
    }

    private function isKesehatanOpd(User $user): bool
    {
        return $user->hasAnyRole([
            AppRole::ADMIN_DINKES->value,
            AppRole::OPERATOR_DINKES->value,
            AppRole::ADMIN_KESEHATAN->value,
            AppRole::OPERATOR_KESEHATAN->value,
            AppRole::ADMIN_PUSKESMAS->value,
            AppRole::OPERATOR_PUSKESMAS->value,
        ]);
    }
}
