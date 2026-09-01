<?php

namespace App\Policies;

use App\Models\DataKesehatanBulanan;
use App\Models\User;
use App\Support\AppRole;

/**
 * A13: Authorization policy for DataKesehatanBulanan.
 *
 * Kabupaten level users (admin, super_admin, admin_kominfo) get full access via `before()`.
 * Kecamatan admins can view all data in their kecamatan but cannot create.
 * OPD-level users (admin_dinkes, operator_dinkes, admin_kesehatan, operator_kesehatan,
 *   admin_puskesmas, operator_puskesmas) can only manage their own created records.
 */
class DataKesehatanBulananPolicy
{
    /**
     * Kabupaten-level users have unrestricted access.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasAnyRole([AppRole::ADMIN->value, AppRole::SUPER_ADMIN->value, AppRole::ADMIN_KOMINFO->value])
            ? true
            : null;
    }

    /**
     * Who can see the list of kesehatan data.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(AppRole::ADMIN_KECAMATAN->value) && $user->kecamatan_id !== null
            || $this->isKesehatanOpd($user);
    }

    /**
     * Who can view a specific kesehatan record.
     */
    public function view(User $user, DataKesehatanBulanan $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isKesehatanOpd($user)) {
            return (int) $record->created_by === (int) $user->id;
        }

        return false;
    }

    /**
     * Who can create new kesehatan records.
     */
    public function create(User $user): bool
    {
        return $this->isKesehatanOpd($user);
    }

    /**
     * Who can update a kesehatan record.
     */
    public function update(User $user, DataKesehatanBulanan $record): bool
    {
        if (! $this->isKesehatanOpd($user)) {
            return false;
        }

        // Only creator can edit (unless super admin via before())
        return (int) $record->created_by === (int) $user->id;
    }

    /**
     * Who can delete a kesehatan record.
     */
    public function delete(User $user, DataKesehatanBulanan $record): bool
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
