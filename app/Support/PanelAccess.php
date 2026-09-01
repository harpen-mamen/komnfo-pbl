<?php

namespace App\Support;

use App\Enums\AppRole;
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

    // ========================
    // SUPER ADMIN & ADMIN
    // ========================
    public static function isSuperAdmin(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::SUPER_ADMIN->value, AppRole::ADMIN->value]);
    }

    public static function canAccessAIAssistant(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::ADMIN->value, AppRole::SUPER_ADMIN->value]);
    }

    // ========================
    // ADMIN KOMINFO (KABUPATEN)
    // ========================
    public static function isKominfoKabupaten(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::ADMIN_KOMINFO->value, AppRole::ADMIN->value]);
    }

    // ========================
    // OPD ADMIN (DINAS/LEMBAGA)
    // ========================

    /**
     * Check if user is an OPD admin (any OPD level)
     */
    public static function isOpdAdmin(): bool
    {
        $user = self::user();

        if (! $user) {
            return false;
        }

        foreach ($user->roles as $role) {
            $enum = AppRole::tryFrom($role->name);
            if ($enum && $enum->isOpdLevel()) {
                return true;
            }
        }

        return false;
    }

    public static function isAdminKesehatan(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::ADMIN_KESEHATAN->value, AppRole::ADMIN_DINKES->value]);
    }

    public static function isOperatorKesehatan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_KESEHATAN->value,
            AppRole::OPERATOR_KESEHATAN->value,
            AppRole::ADMIN_DINKES->value,
            AppRole::OPERATOR_DINKES->value,
        ]);
    }

    public static function isAdminPendidikan(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_PENDIDIKAN->value);
    }

    public static function isOperatorPendidikan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_PENDIDIKAN->value,
            AppRole::OPERATOR_PENDIDIKAN->value,
        ]);
    }

    public static function isAdminPU(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::ADMIN_PU->value, AppRole::ADMIN_DPU->value]);
    }

    public static function isOperatorPU(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_PU->value,
            AppRole::OPERATOR_PU->value,
            AppRole::ADMIN_DPU->value,
            AppRole::OPERATOR_DPU->value,
        ]);
    }

    public static function isAdminSosial(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::ADMIN_SOSIAL->value, AppRole::ADMIN_DINSOS->value]);
    }

    public static function isOperatorSosial(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_SOSIAL->value,
            AppRole::OPERATOR_SOSIAL->value,
            AppRole::ADMIN_DINSOS->value,
            AppRole::OPERATOR_DINSOS->value,
        ]);
    }

    public static function isAdminEkonomi(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_EKONOMI->value,
            AppRole::ADMIN_DISPEND->value,
            AppRole::ADMIN_DISPERINDAG->value,
        ]);
    }

    public static function isOperatorEkonomi(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_EKONOMI->value,
            AppRole::OPERATOR_EKONOMI->value,
            AppRole::ADMIN_DISPEND->value,
            AppRole::OPERATOR_DISPEND->value,
            AppRole::ADMIN_DISPERINDAG->value,
            AppRole::OPERATOR_DISPERINDAG->value,
        ]);
    }

    public static function isAdminKependudukan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_KEPENDUDUKAN->value,
            AppRole::ADMIN_DUKCAPIL->value,
        ]);
    }

    public static function isOperatorKependudukan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_KEPENDUDUKAN->value,
            AppRole::OPERATOR_KEPENDUDUKAN->value,
            AppRole::ADMIN_DUKCAPIL->value,
            AppRole::OPERATOR_DUKCAPIL->value,
        ]);
    }

    public static function isAdminLingkungan(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::ADMIN_LINGKUNGAN->value, AppRole::ADMIN_DLH->value]);
    }

    public static function isOperatorLingkungan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_LINGKUNGAN->value,
            AppRole::OPERATOR_LINGKUNGAN->value,
            AppRole::ADMIN_DLH->value,
            AppRole::OPERATOR_DLH->value,
        ]);
    }

    // ========================
    // ADMIN KECAMATAN & DESA
    // ========================
    public static function isAdminKecamatan(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_KECAMATAN->value);
    }

    public static function isAdminDesa(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_DESA->value);
    }

    public static function isOperatorSekolah(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::OPERATOR_SEKOLAH->value, AppRole::ADMIN_SEKOLAH->value]);
    }

    public static function isOperatorPuskesmas(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::OPERATOR_PUSKESMAS->value, AppRole::ADMIN_PUSKESMAS->value]);
    }

    public static function isOperatorPuskesmus(): bool
    {
        return (bool) self::user()?->hasAnyRole([AppRole::OPERATOR_PUSKESMAS->value, AppRole::ADMIN_PUSKESMAS->value]);
    }

    public static function isAdminSekolahKecamatan(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_SEKOLAH->value);
    }

    public static function isAdminPuskes(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_PUSKESMAS->value);
    }

    public static function canAccessPuskes(?int $puskesId = null): bool
    {
        $user = self::user();
        if (!$user) return false;
        if (self::isSuperAdmin() || self::isKominfoKabupaten()) return true;
        if (self::isAdminPuskes() || self::isOperatorPuskesmus()) {
            return $puskesId === null || $user->puskes_id === $puskesId;
        }
        return false;
    }

    public static function canAccessSekolah(?int $kecamatanId = null): bool
    {
        $user = self::user();
        if (!$user) return false;
        if (self::isSuperAdmin() || self::isKominfoKabupaten()) return true;
        if (self::isAdminSekolahKecamatan() || self::isOperatorSekolah()) {
            return $kecamatanId === null || $user->kecamatan_id === $kecamatanId;
        }
        return false;
    }

    public static function isOperatorOpd(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_OPD->value);
    }

    // ========================
    // PANEL ACCESS CHECKS
    // ========================

    /**
     * Can access Kabupaten/Kota level panel
     */
    public static function canAccessKabupatenPanel(): bool
    {
        return self::isSuperAdmin() || self::isKominfoKabupaten();
    }

    /**
     * Can access Kecamatan level panel
     */
    public static function canAccessKecamatanPanel(): bool
    {
        return self::isAdminKecamatan() || self::isAdminDesa();
    }

    /**
     * Can access OPD level panel
     */
    public static function canAccessOpdPanel(): bool
    {
        return self::isOpdAdmin();
    }

    /**
     * Can access Operator level panel
     */
    public static function canAccessOperatorPanel(): bool
    {
        return self::isOperatorOpd()
            || self::isOperatorKesehatan()
            || self::isOperatorPendidikan()
            || self::isOperatorPU()
            || self::isOperatorSosial()
            || self::isOperatorEkonomi()
            || self::isOperatorKependudukan()
            || self::isOperatorLingkungan();
    }

    // ========================
    // PERMISSION CHECKS
    // ========================

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

    public static function canApproveOpdBatch(string $opdModule): bool
    {
        $user = self::user();

        if (!$user) {
            return false;
        }

        // Super admin dan kominfo bisa approve semua
        if (self::isSuperAdmin() || self::isKominfoKabupaten()) {
            return true;
        }

        // Check berdasarkan OPD module
        return match ($opdModule) {
            'kesehatan' => self::isAdminKesehatan(),
            'pendidikan' => self::isAdminPendidikan(),
            'pu' => self::isAdminPU(),
            'sosial' => self::isAdminSosial(),
            'ekonomi' => self::isAdminEkonomi(),
            'kependudukan' => self::isAdminKependudukan(),
            'lingkungan' => self::isAdminLingkungan(),
            default => false,
        };
    }
}
