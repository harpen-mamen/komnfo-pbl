<?php

namespace App\Policies;

use App\Models\Bayi;
use App\Models\User;
use App\Support\AppRole;

/**
 * A14: Authorization policy for Bayi (health sub-record of DataKesehatanBulanan).
 *
 * Access pattern (mirrors DataKesehatanBulananPolicy):
 * - Kabupaten level (admin, super_admin, admin_kominfo): full access via before().
 * - Admin kecamatan: read-only in their kecamatan.
 * - Operator/admin puskes & Dinkes: full CRUD on records they created.
 */
class BayiPolicy
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

    public function view(User $user, Bayi $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isKesehatanOpd($user)) {
            if ($user->puskes_id && (int) $record->fasilitas_kesehatan_id === (int) $user->puskes_id) {
                return true;
            }
            return (int) $record->created_by === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isKesehatanOpd($user);
    }

    public function update(User $user, Bayi $record): bool
    {
        if (! $this->isKesehatanOpd($user)) {
            return false;
        }

        if ($user->puskes_id) {
            return (int) $record->fasilitas_kesehatan_id === (int) $user->puskes_id;
        }

        return (int) $record->created_by === (int) $user->id;
    }

    public function delete(User $user, Bayi $record): bool
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
