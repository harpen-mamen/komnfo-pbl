<?php

namespace App\Policies;

use App\Models\StokObat;
use App\Models\User;
use App\Support\AppRole;

/**
 * A14: Authorization policy for StokObat (inventory at puskes).
 *
 * Access pattern:
 * - Kabupaten level: full access.
 * - Admin kecamatan: read-only (within kecamatan via puskes.kecamatan_id).
 * - Dinkes operator/admin: CRUD across all puskes in kabupaten.
 * - Puskes admin/operator: CRUD only within their own facility.
 */
class StokObatPolicy
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

    public function view(User $user, StokObat $record): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $record->fasilitasKesehatan?->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($this->isKesehatanOpd($user)) {
            // Puskes user: only their own puskes
            if ($user->puskes_id) {
                return (int) $record->fasilitas_kesehatan_id === (int) $user->puskes_id;
            }

            // Dinkes user: all puskes (their scope)
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isKesehatanOpd($user);
    }

    public function update(User $user, StokObat $record): bool
    {
        return $this->view($user, $record);
    }

    public function delete(User $user, StokObat $record): bool
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
