<?php

namespace App\Policies;

use App\Models\TenagaKesehatan;
use App\Models\User;
use App\Support\AppRole;

/**
 * A14: Authorization policy for TenagaKesehatan.
 *
 * Access pattern:
 * - Kabupaten level: full access via before().
 * - Admin kecamatan: read-only in their kecamatan (via puskes.kecamatan_id).
 * - Dinkes admin/operator & admin puskes: CRUD in scope of their puskes.
 */
class TenagaKesehatanPolicy
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

    public function view(User $user, TenagaKesehatan $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isKesehatanOpd($user)) {
            // Puskes users scoped by fasilitas_kesehatan_id
            if ($user->puskes_id && $record->fasilitas_kesehatan_id !== null) {
                return (int) $record->fasilitas_kesehatan_id === (int) $user->puskes_id;
            }

            // Dinkes users see records created within their scope
            return (int) $record->created_by === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isKesehatanOpd($user);
    }

    public function update(User $user, TenagaKesehatan $record): bool
    {
        return $this->view($user, $record);
    }

    public function delete(User $user, TenagaKesehatan $record): bool
    {
        return $this->view($user, $record);
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
