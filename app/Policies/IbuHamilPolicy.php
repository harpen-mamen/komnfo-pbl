<?php

namespace App\Policies;

use App\Models\IbuHamil;
use App\Models\User;
use App\Support\AppRole;

/**
 * A14: Authorization policy for IbuHamil (kesehatan sub-record).
 */
class IbuHamilPolicy
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

    public function view(User $user, IbuHamil $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isKesehatanOpd($user)) {
            return (int) $record->created_by === (int) $user->id
                || $this->userCanAccessFaskesRecord($user, $record);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isKesehatanOpd($user);
    }

    public function update(User $user, IbuHamil $record): bool
    {
        if (! $this->isKesehatanOpd($user)) {
            return false;
        }

        if ($user->puskes_id) {
            return (int) $record->kecamatan_id === (int) optional($user->puskes)->kecamatan_id;
        }

        return (int) $record->created_by === (int) $user->id;
    }

    public function delete(User $user, IbuHamil $record): bool
    {
        return $this->update($user, $record);
    }

    private function userCanAccessFaskesRecord(User $user, IbuHamil $record): bool
    {
        if (! $user->puskes_id) {
            return false;
        }

        return (int) $record->kecamatan_id === (int) optional($user->puskes)->kecamatan_id;
    }

    private function isKesehatanOpd(User $user): bool
    {
        return $user->hasAnyRole([
            AppRole::ADMIN_DINKES->value,
            AppRole::OPERATOR_DINKES->value,
            
            
            
            
        ]);
    }
}
