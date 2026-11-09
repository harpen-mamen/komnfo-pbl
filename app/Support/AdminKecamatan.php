<?php

namespace App\Support;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdminKecamatan
{
    public static function user(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }

    public static function kecamatanId(): ?int
    {
        $user = self::user();

        // If user has direct kecamatan_id, use it
        if ($user?->kecamatan_id) {
            return $user->kecamatan_id;
        }

        // Otherwise, get kecamatan_id from user's desa via parent hierarchy
        if ($user?->desa_id) {
            $desa = Wilayah::query()->find($user->desa_id);
            return $desa?->parent_id;
        }

        return null;
    }

    public static function desaId(): ?int
    {
        return self::user()?->desa_id;
    }

    public static function isAdminKecamatan(): bool
    {
        return (bool) self::user()?->isAdminKecamatan();
    }

    public static function isAdminDesa(): bool
    {
        return (bool) self::user()?->isAdminDesa();
    }

    public static function isAdminLapangan(): bool
    {
        return (bool) self::user()?->isAdminLapangan();
    }

    public static function kecamatan(): ?Wilayah
    {
        $kecamatanId = self::kecamatanId();

        return $kecamatanId ? Wilayah::query()->find($kecamatanId) : null;
    }

    public static function desaOptions()
    {
        return Wilayah::query()
            ->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])
            ->where('aktif', true)
            ->when(self::isAdminKecamatan(), fn (Builder $query) => $query->where('parent_id', self::kecamatanId()))
            ->when(self::isAdminDesa(), fn (Builder $query) => $query->whereKey(self::desaId()))
            ->when(self::isAdminLapangan() && self::desaId(), fn (Builder $query) => $query->whereKey(self::desaId()))
            ->when(self::isAdminLapangan() && ! self::desaId(), fn (Builder $query) => $query->where('parent_id', self::kecamatanId()))
            ->orderBy('nama')
            ->pluck('nama', 'id');
    }

    public static function canUseDesa(?int $desaId): bool
    {
        if (! $desaId) {
            return false;
        }

        $desa = Wilayah::query()->where('tipe', Wilayah::TIPE_DESA)->find($desaId);

        if (! $desa) {
            return false;
        }

        if (self::isAdminDesa()) {
            return (int) self::desaId() === $desaId;
        }

        if (self::isAdminLapangan()) {
            if (self::desaId()) {
                return (int) self::desaId() === $desaId;
            }

            // Admin lapangan without specific desa assignment can access all desas in their kecamatan
            return $desa->parent_id === self::kecamatanId();
        }

        if (! self::isAdminKecamatan()) {
            return true;
        }

        return $desa->parent_id === self::kecamatanId();
    }

    public static function scopeRumahToKecamatan(Builder $query): Builder
    {
        if (self::isAdminKecamatan()) {
            // rumah.desa_id points to wilayahs.id (tipe=desa), join to get parent_id (kecamatan)
            $query->whereHas('desa', fn (Builder $desa) => $desa->where('parent_id', self::kecamatanId()));
        } elseif (self::isAdminDesa()) {
            $query->where('desa_id', self::desaId());
        }

        return $query;
    }

    public static function scopeDesaToKecamatan(Builder $query): Builder
    {
        if (self::isAdminKecamatan()) {
            // For wilayahs table, filter by parent_id (kecamatan)
            $query->where('parent_id', self::kecamatanId());
        } elseif (self::isAdminDesa()) {
            $query->whereKey(self::desaId());
        }

        return $query;
    }
}
