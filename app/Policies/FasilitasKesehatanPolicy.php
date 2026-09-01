<?php

namespace App\Policies;

use App\Models\FasilitasKesehatan;
use App\Models\User;
use App\Support\AppRole;

/**
 * A14: Authorization policy for FasilitasKesehatan (master puskes/pustu/posyandu).
 *
 * Access pattern:
 * - Kabupaten level: full access via before().
 * - Admin kecamatan: read-only in their kecamatan.
 * - Dinkes & puskes admin: CRUD within their scope (own puskes for puskes admin,
 *   all puskes in kabupaten for Dinkes).
 */
class FasilitasKesehatanPolicy
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

    public function view(User $user, FasilitasKesehatan $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isKesehatanOpd($user)) {
            // Puskes admin/operator: only their own facility
            if ($user->puskes_id) {
                return (int) $record->id === (int) $user->puskes_id;
            }

            // Dinkes level: all puskes (their scope)
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isDinkesLevel($user);
    }

    public function update(User $user, FasilitasKesehatan $record): bool
    {
        return $this->isDinkesLevel($user);
    }

    public function delete(User $user, FasilitasKesehatan $record): bool
    {
        return $this->isDinkesLevel($user);
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

    private function isDinkesLevel(User $user): bool
    {
        return $user->hasAnyRole([
            AppRole::ADMIN_DINKES->value,
            AppRole::ADMIN_KESEHATAN->value,
        ]);
    }
}
