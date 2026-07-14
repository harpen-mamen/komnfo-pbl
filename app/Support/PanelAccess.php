<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PanelAccess
{
    public static function user(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }

    public static function isSuperAdmin(): bool
    {
        return (bool) self::user()?->hasAnyRole(['super_admin', 'admin']);
    }

    public static function isKominfoKabupaten(): bool
    {
        return (bool) self::user()?->hasAnyRole(['admin_kominfo', 'admin']);
    }

    public static function isAdminKecamatan(): bool
    {
        return (bool) self::user()?->hasRole('admin_kecamatan');
    }

    public static function isAdminDesa(): bool
    {
        return (bool) self::user()?->hasRole('admin_desa');
    }

    public static function isOperatorSekolah(): bool
    {
        return false;
    }

    public static function isOperatorPuskesmas(): bool
    {
        return false;
    }

    public static function isOperatorOpd(): bool
    {
        return (bool) self::user()?->hasRole('admin_opd');
    }

    public static function canAccessKabupatenPanel(): bool
    {
        return self::isSuperAdmin() || self::isKominfoKabupaten();
    }

    public static function canAccessKecamatanPanel(): bool
    {
        return self::isAdminKecamatan() || self::isAdminDesa();
    }

    public static function canAccessOperatorPanel(): bool
    {
        return self::isOperatorOpd();
    }

    public static function canManageUsers(): bool
    {
        return self::isSuperAdmin() || self::isKominfoKabupaten();
    }

    public static function canReviewKecamatan(): bool
    {
        return self::isAdminKecamatan() || self::isSuperAdmin();
    }

    public static function canValidateKominfo(): bool
    {
        return self::isKominfoKabupaten() || self::isSuperAdmin();
    }
}
