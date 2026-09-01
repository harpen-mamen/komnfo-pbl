<?php

namespace App\Policies;

use App\Models\DataPendidikanBulanan;
use App\Models\User;
use App\Support\AppRole;

/**
 * A13: Authorization policy for DataPendidikanBulanan.
 *
 * Kabupaten-level users (admin, super_admin, admin_kominfo) get full access via `before()`.
 * Kecamatan admins can view all data in their kecamatan.
 * OPD-level users (admin_dispend, operator_dispend, admin_pendidikan, operator_pendidikan,
 *   admin_sekolah, operator_sekolah) can manage their own created records.
 */
class DataPendidikanBulananPolicy
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
            || $this->isPendidikanOpd($user);
    }

    public function view(User $user, DataPendidikanBulanan $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isPendidikanOpd($user)) {
            return (int) $record->created_by === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isPendidikanOpd($user);
    }

    public function update(User $user, DataPendidikanBulanan $record): bool
    {
        if (! $this->isPendidikanOpd($user)) {
            return false;
        }

        return (int) $record->created_by === (int) $user->id;
    }

    public function delete(User $user, DataPendidikanBulanan $record): bool
    {
        return $this->update($user, $record);
    }

    private function isPendidikanOpd(User $user): bool
    {
        return $user->hasAnyRole([
            AppRole::ADMIN_DISPEND->value,
            AppRole::OPERATOR_DISPEND->value,
            AppRole::ADMIN_PENDIDIKAN->value,
            AppRole::OPERATOR_PENDIDIKAN->value,
            AppRole::ADMIN_SEKOLAH->value,
            AppRole::OPERATOR_SEKOLAH->value,
        ]);
    }
}
