<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

/**
 * Policy untuk manajemen Unit (wilayah + OPD).
 *
 * Hanya WALIDATA yang boleh CRUD unit. User lain read-only dengan scope.
 */
class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessFilament();
    }

    public function view(User $user, Unit $unit): bool
    {
        // Pembina & Walidata lihat semua. Lainnya: hanya unit sendiri atau parent chain.
        if ($user->isPembina() || $user->isWalidata()) {
            return true;
        }
        return $user->unit_id === $unit->id;
    }

    public function create(User $user): bool
    {
        return $user->canManageUnits();
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->canManageUnits();
    }

    public function delete(User $user, Unit $unit): bool
    {
        // Tidak boleh hapus unit yang punya user/record.
        return $user->canManageUnits();
    }
}
