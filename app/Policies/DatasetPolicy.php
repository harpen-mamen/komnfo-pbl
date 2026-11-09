<?php

namespace App\Policies;

use App\Models\DATA_SEKTORAL\Dataset;
use App\Models\User;

/**
 * Policy untuk katalog dataset + metadata SDI.
 *
 * - WALIDATA: CRUD penuh.
 * - ADMIN_OPD / OPERATOR_OPD / KEPALA_DINAS: CRUD dataset untuk unit sendiri.
 * - PEMBINA: read-only lintas semua.
 * - ADMIN_KECAMATAN / ADMIN_DESA: read-only.
 */
class DatasetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessFilament();
    }

    public function view(User $user, Dataset $dataset): bool
    {
        if ($user->isPembina() || $user->isWalidata()) {
            return true;
        }
        if ($user->role?->isOpdLevel() === true) {
            return $dataset->unit_id === $user->unit_id;
        }
        return false; // kecamatan/desa tidak akses dataset
    }

    public function create(User $user): bool
    {
        return $user->role?->canManageDatasets() ?? false;
    }

    public function update(User $user, Dataset $dataset): bool
    {
        if ($user->isWalidata()) {
            return true;
        }
        if ($user->role?->isOpdLevel() === true) {
            return $dataset->unit_id === $user->unit_id;
        }
        return false;
    }

    public function delete(User $user, Dataset $dataset): bool
    {
        return $user->isWalidata(); // hanya walidata boleh hapus katalog
    }

    public function verifyForum(User $user, Dataset $dataset): bool
    {
        // Hanya Pembina (Forum Satu Data) yang verifikasi metadata.
        return $user->isPembina();
    }
}
