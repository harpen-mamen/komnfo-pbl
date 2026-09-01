<?php

namespace App\Support;

use App\Enums\AppRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * SPEC-004: Single source of truth for data visibility scoping.
 *
 * PURPOSE:
 * Before: scope visibility was implemented in 3 different patterns across models:
 *   - AdminKecamatan class (static methods) for Rumah/Keluarga/Penduduk
 *   - PanelAccess class (static methods) for DataKesehatanBulanan/DataPendidikanBulanan
 *   - Inline closure for SubmissionBatch
 *
 * After: ALL models use UserScope::visibleTo() — ONE consistent pattern.
 *
 * PRINCIPLE:
 * - UserScope handles DATA VISIBILITY (what data can user see)
 * - PanelAccess handles AUTHORIZATION (what actions can user perform)
 * - These are orthogonal concerns and remain separate.
 *
 * SCOPE RULES:
 * - Kabupaten level (super_admin, admin, admin_kominfo): see ALL data
 * - Kecamatan level (admin_kecamatan): see data in their kecamatan
 * - Desa level (admin_desa): see data in their desa
 * - OPD level (operator_puskes, operator_sekolah, dll): see data they created
 */
class UserScope
{
    /**
     * Get the current authenticated user.
     */
    public static function user(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }

    /**
     * Get user's kecamatan_id — from user record or from their assigned desa.
     */
    public static function getKecamatanId(): ?int
    {
        $user = self::user();

        return $user?->kecamatan_id ?? $user?->desa?->kecamatan_id;
    }

    /**
     * Get user's desa_id.
     */
    public static function getDesaId(): ?int
    {
        return self::user()?->desa_id;
    }

    /**
     * Check if user is at kabupaten level (can see all data).
     */
    public static function isKabupatenLevel(): bool
    {
        $user = self::user();

        return AppRole::tryFrom($user?->roles->first()?->name ?? '')?->isKabupatenLevel() ?? false;
    }

    /**
     * Check if user is at kecamatan level.
     */
    public static function isKecamatanLevel(): bool
    {
        $user = self::user();

        return AppRole::tryFrom($user?->roles->first()?->name ?? '')?->isKecamatanLevel() ?? false;
    }

    /**
     * Check if user is at desa level.
     */
    public static function isDesaLevel(): bool
    {
        $user = self::user();

        return AppRole::tryFrom($user?->roles->first()?->name ?? '')?->isDesaLevel() ?? false;
    }

    /**
     * Check if user is at OPD level (operator_puskes, operator_sekolah, dll).
     */
    public static function isOpdLevel(): bool
    {
        $user = self::user();

        if (! $user) {
            return false;
        }

        // Check if any of the user's roles is an OPD level role.
        foreach ($user->roles as $role) {
            if (AppRole::tryFrom($role->name)?->isOpdLevel()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is puskes-level (admin_puskes atau operator_puskes).
     */
    public static function isPuskesLevel(): bool
    {
        $user = self::user();

        if (! $user) {
            return false;
        }

        foreach ($user->roles as $role) {
            $name = $role->name;
            if ($name === AppRole::ADMIN_PUSKESMAS->value || $name === AppRole::OPERATOR_PUSKESMAS->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is sekolah-level (admin_sekolah atau operator_sekolah).
     */
    public static function isSekolahLevel(): bool
    {
        $user = self::user();

        if (! $user) {
            return false;
        }

        foreach ($user->roles as $role) {
            $name = $role->name;
            if ($name === AppRole::ADMIN_SEKOLAH->value || $name === AppRole::OPERATOR_SEKOLAH->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply the appropriate visibility scope to a query.
     * This is the SINGLE ENTRY POINT for all model scope visibility.
     *
     * Auto-detects the model structure by inspecting the table columns/relations
     * and applies the appropriate filter.
     *
     * USAGE:
     *   // In any model:
     *   public function scopeVisibleToCurrentUser(Builder $query)
     *   {
     *       return UserScope::visibleTo($query);
     *   }
     */
    public static function visibleTo(Builder $query): Builder
    {
        $user = self::user();

        // Kabupaten level: no restriction
        if (self::isKabupatenLevel()) {
            return $query;
        }

        $table = $query->getModel()->getTable();
        $hasKecamatanId = self::hasColumn($table, 'kecamatan_id');
        $hasDesaId = self::hasColumn($table, 'desa_id');
        $hasCreatedBy = self::hasColumn($table, 'created_by');

        // Kecamatan level: filter by kecamatan_id
        if (self::isKecamatanLevel()) {
            $kecamatanId = self::getKecamatanId();

            // Tables that have kecamatan_id directly
            if ($hasKecamatanId) {
                return $query->where('kecamatan_id', $kecamatanId);
            }

            // Tables that access through a `rumah` relation (e.g., Keluarga, Penduduk, Bayi, etc.)
            if (method_exists($query->getModel(), 'rumah')) {
                return $query->whereHas('rumah.desa', fn ($q) =>
                    $q->where('kecamatan_id', $kecamatanId)
                );
            }

            // Tables with `desa_id` column (e.g., Rumah) → filter via desa.kecamatan_id
            if ($hasDesaId && method_exists($query->getModel(), 'desa')) {
                return $query->whereHas('desa', fn ($q) =>
                    $q->where('kecamatan_id', $kecamatanId)
                );
            }

            // Tables with `keluarga_id` column (e.g., Penduduk) → filter via keluarga.rumah.desa
            if (self::hasColumn($table, 'keluarga_id') && method_exists($query->getModel(), 'keluarga')) {
                return $query->whereHas('keluarga.rumah.desa', fn ($q) =>
                    $q->where('kecamatan_id', $kecamatanId)
                );
            }

            // Fallback: if neither, return empty
            return $query->whereRaw('1 = 0');
        }

        // Desa level: filter by desa_id
        if (self::isDesaLevel()) {
            $desaId = self::getDesaId();

            // Tables that have desa_id directly
            if ($hasDesaId) {
                return $query->where('desa_id', $desaId);
            }

            // Tables that access through a relation
            if (method_exists($query->getModel(), 'rumah')) {
                return $query->whereHas('rumah', fn ($q) =>
                    $q->where('desa_id', $desaId)
                );
            }

            // Tables with `rumah_id` column but no direct desa_id
            if (self::hasColumn($table, 'rumah_id')) {
                return $query->whereHas('rumah', fn ($q) =>
                    $q->where('desa_id', $desaId)
                );
            }

            return $query->whereRaw('1 = 0');
        }

        // OPD level: see only data created by self
        if (self::isOpdLevel()) {
            if ($hasCreatedBy) {
                return $query->where('created_by', $user?->id);
            }

            return $query->whereRaw('1 = 0');
        }

        // Puskes level: see data in their puskes facility only
        if (self::isPuskesLevel()) {
            $table = $query->getModel()->getTable();
            // FasilitasKesehatan: filter by self (they ARE the facility)
            if ($table === 'fasilitas_kesehatans' && method_exists($query->getModel(), 'creator')) {
                return $query->where('created_by', $user?->id);
            }
            // StokObat, RekamMedis: filter by fasilitas_kesehatan_id
            if (self::hasColumn($table, 'fasilitas_kesehatan_id') && $user?->puskes_id) {
                return $query->where('fasilitas_kesehatan_id', $user->puskes_id);
            }
            // TenagaKesehatan: filter by fasilitas_kesehatan_id
            if ($table === 'tenaga_kesehatans' && $user?->puskes_id) {
                return $query->where('fasilitas_kesehatan_id', $user->puskes_id);
            }
            return $query->whereRaw('1 = 0');
        }

        // Sekolah level: see data in their kecamatan only
        if (self::isSekolahLevel()) {
            $kecamatanId = self::getKecamatanId();
            // Sekolah: filter by kecamatan_id
            if (self::hasColumn($query->getModel()->getTable(), 'kecamatan_id')) {
                return $query->where('kecamatan_id', $kecamatanId);
            }
            // DataGuru, DataSiswa, DataPrasarana: filter through sekolah relation
            if (method_exists($query->getModel(), 'sekolah')) {
                return $query->whereHas('sekolah', fn ($q) =>
                    $q->where('kecamatan_id', $kecamatanId)
                );
            }
            return $query->whereRaw('1 = 0');
        }

        // Fallback: no access
        return $query->whereRaw('1 = 0');
    }

    /**
     * Helper: check if a table has a column.
     * Results are cached per request to avoid repeated schema queries.
     */
    public static function hasColumn(string $table, string $column): bool
    {
        static $cache = [];

        $key = "{$table}.{$column}";

        if (! isset($cache[$key])) {
            $cache[$key] = Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }
}
