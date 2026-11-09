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
        return (bool) self::user()?->hasAnyRole([
            AppRole::WALIDATA->value,
            AppRole::ADMIN_KOMINFO->value,
            AppRole::ADMIN->value,
        ]);
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
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DINKES->value,
            AppRole::OPERATOR_DINKES->value,
        ]);
    }

    public static function isOperatorKesehatan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DINKES->value,
            AppRole::OPERATOR_DINKES->value,
        ]);
    }

    public static function isAdminPendidikan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DISPEND->value,
            AppRole::OPERATOR_DISPEND->value,
        ]);
    }

    public static function isOperatorPendidikan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DISPEND->value,
            AppRole::OPERATOR_DISPEND->value,
        ]);
    }

    public static function isAdminPU(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DPU->value,
            AppRole::OPERATOR_DPU->value,
        ]);
    }

    public static function isOperatorPU(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DPU->value,
            AppRole::OPERATOR_DPU->value,
        ]);
    }

    public static function isAdminSosial(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DINSOS->value,
            AppRole::OPERATOR_DINSOS->value,
        ]);
    }

    public static function isOperatorSosial(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DINSOS->value,
            AppRole::OPERATOR_DINSOS->value,
        ]);
    }

    public static function isAdminEkonomi(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DISPERINDAG->value,
            AppRole::OPERATOR_DISPERINDAG->value,
        ]);
    }

    public static function isOperatorEkonomi(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DISPERINDAG->value,
            AppRole::OPERATOR_DISPERINDAG->value,
        ]);
    }

    public static function isAdminKependudukan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DUKCAPIL->value,
            AppRole::OPERATOR_DUKCAPIL->value,
        ]);
    }

    public static function isOperatorKependudukan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DUKCAPIL->value,
            AppRole::OPERATOR_DUKCAPIL->value,
        ]);
    }

    public static function isAdminLingkungan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
            AppRole::ADMIN_DLH->value,
            AppRole::OPERATOR_DLH->value,
        ]);
    }

    public static function isOperatorLingkungan(): bool
    {
        return (bool) self::user()?->hasAnyRole([
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

    public static function isAdminLapangan(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_LAPANGAN->value);
    }

    public static function isKependudukanDataOwner(): bool
    {
        $user = self::user();
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole([
            AppRole::ADMIN_DUKCAPIL->value,
            AppRole::OPERATOR_DUKCAPIL->value,
        ]);
    }

    // ========================
    // ADMIN BERITA
    // ========================
    public static function isAdminBerita(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_BERITA->value);
    }

    public static function isAdminPortal(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::ADMIN_PORTAL->value);
    }

    // ========================
    // KEPALA DINAS
    // ========================
    public static function isKepalaDinas(): bool
    {
        return (bool) self::user()?->hasRole(AppRole::KEPALA_DINAS->value);
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

    public static function canAccessLapanganPanel(): bool
    {
        return self::isAdminLapangan();
    }

    /**
     * Can access OPD level panel
     */
    public static function canAccessOpdPanel(): bool
    {
        return self::isOpdAdmin() || self::isKepalaDinas();
    }

    /**
     * Can access berita/content panel
     */
    public static function canAccessBeritaPanel(): bool
    {
        return self::isAdminBerita() || self::isSuperAdmin() || self::isKominfoKabupaten();
    }

    public static function canManagePortal(): bool
    {
        return self::isAdminPortal();
    }

    /**
     * Can access operator panel (OPD operator level)
     * Includes all OPD admin and operator roles
     */
    public static function canAccessOperatorPanel(): bool
    {
        return self::isOpdAdmin()
            || self::isKepalaDinas()
            || self::isAdminKesehatan()
            || self::isAdminPendidikan()
            || self::isAdminPU()
            || self::isAdminSosial()
            || self::isAdminEkonomi()
            || self::isAdminKependudukan()
            || self::isAdminLingkungan();
    }

    // ========================
    // DEPRECATED METHODS (Legacy support)
    // Keep for backward compatibility
    // ========================

    /**
     * @deprecated Use isAdminKesehatan() instead
     */
    public static function isAdminPuskes(): bool
    {
        return self::isAdminKesehatan();
    }

    /**
     * @deprecated Use isOperatorKesehatan() instead
     */
    public static function isOperatorPuskesmus(): bool
    {
        return self::isOperatorKesehatan();
    }

    /**
     * @deprecated Use isOperatorKesehatan() instead
     */
    public static function isOperatorPuskes(): bool
    {
        return self::isOperatorKesehatan();
    }

    /**
     * @deprecated Use isAdminPendidikan() instead
     */
    public static function isAdminSekolahKecamatan(): bool
    {
        return self::isAdminPendidikan();
    }

    /**
     * @deprecated Use isAdminPendidikan() instead
     */
    public static function isOperatorSekolah(): bool
    {
        return self::isOperatorPendidikan();
    }

    /**
     * @deprecated Use isOpdAdmin() instead
     */
    public static function isOperatorOpd(): bool
    {
        return self::isOpdAdmin();
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

    /**
     * Can approve any data (for approval workflow)
     */
    public static function canApproveAny(): bool
    {
        return self::isSuperAdmin() || self::isKominfoKabupaten() || self::isKepalaDinas();
    }

    /**
     * Can manage public content/berita
     */
    public static function canManagePublicContent(): bool
    {
        return self::isAdminBerita() || self::isSuperAdmin() || self::isKominfoKabupaten();
    }
}
