<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy untuk manajemen user.
 *
 * Hanya WALIDATA yang boleh mengelola user. PEMBINA read-only.
 * User biasa: hanya boleh edit profil sendiri (lihat EditProfile).
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isWalidata();
    }

    public function view(User $user, User $target): bool
    {
        // Self selalu boleh. Lainnya: hanya walidata.
        return $user->id === $target->id || $user->isWalidata();
    }

    public function create(User $user): bool
    {
        return $user->isWalidata();
    }

    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->isWalidata();
    }

    public function delete(User $user, User $target): bool
    {
        // Tidak boleh hapus diri sendiri; hanya walidata boleh hapus user lain.
        return $user->id !== $target->id && $user->isWalidata();
    }

    /** Update profil sendiri — semua role boleh. */
    public function updateOwnProfile(User $user): bool
    {
        return $user->canAccessFilament();
    }
}
