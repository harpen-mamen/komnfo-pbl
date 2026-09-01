<?php

namespace App\Models\Concerns;

use App\Models\Opd;
use App\Models\User;
use App\Support\PanelAccess;
use Illuminate\Database\Eloquent\Builder;

trait HasOpdModule
{
    /**
     * Get the OPD that owns this data
     */
    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Scope to filter by current user's OPD
     */
    public function scopeForCurrentOpd(Builder $query): Builder
    {
        $user = PanelAccess::user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Super admin and kominfo can see all
        if (PanelAccess::isSuperAdmin() || PanelAccess::isKominfoKabupaten()) {
            return $query;
        }

        // OPD users only see their OPD's data
        if ($user->opd_id) {
            return $query->where('opd_id', $user->opd_id);
        }

        // Admin kecamatan can see data from their kecamatan
        if (PanelAccess::isAdminKecamatan()) {
            return $query->where('kecamatan_id', $user->kecamatan_id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope to filter by OPD module type
     */
    public function scopeForModule(Builder $query, string $module): Builder
    {
        $opd = Opd::where('kategori', $module)->first();
        
        if ($opd) {
            return $query->where('opd_id', $opd->id);
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Check if user can access this record
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Super admin and kominfo
        if ($user->hasAnyRole(['super_admin', 'admin', 'admin_kominfo'])) {
            return true;
        }

        // Same OPD
        if ($user->opd_id && $user->opd_id === $this->opd_id) {
            return true;
        }

        // Admin kecamatan
        if ($user->hasRole('admin_kecamatan')) {
            return $this->kecamatan_id === $user->kecamatan_id;
        }

        return false;
    }

    /**
     * Get module name based on OPD kategori
     */
    public function getModuleName(): string
    {
        return $this->opd?->kategori ?? 'lainnya';
    }
}
