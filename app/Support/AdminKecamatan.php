<?php

namespace App\Support;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\User;
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
        return self::user()?->kecamatan_id;
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

    public static function kecamatan(): ?Kecamatan
    {
        $kecamatanId = self::kecamatanId();

        return $kecamatanId ? Kecamatan::query()->find($kecamatanId) : null;
    }

    public static function desaOptions()
    {
        return Desa::query()
            ->when(self::isAdminKecamatan(), fn (Builder $query) => $query->where('kecamatan_id', self::kecamatanId()))
            ->when(self::isAdminDesa(), fn (Builder $query) => $query->whereKey(self::desaId()))
            ->orderBy('nama')
            ->pluck('nama', 'id');
    }

    public static function canUseDesa(?int $desaId): bool
    {
        if (! $desaId) {
            return false;
        }

        if (self::isAdminDesa()) {
            return (int) self::desaId() === $desaId;
        }

        if (! self::isAdminKecamatan()) {
            return true;
        }

        return Desa::query()
            ->whereKey($desaId)
            ->where('kecamatan_id', self::kecamatanId())
            ->exists();
    }

    public static function scopeRumahToKecamatan(Builder $query): Builder
    {
        if (self::isAdminKecamatan()) {
            $query->whereHas('desa', fn (Builder $desa) => $desa->where('kecamatan_id', self::kecamatanId()));
        } elseif (self::isAdminDesa()) {
            $query->where('desa_id', self::desaId());
        }

        return $query;
    }

    public static function scopeDesaToKecamatan(Builder $query): Builder
    {
        if (self::isAdminKecamatan()) {
            $query->where('kecamatan_id', self::kecamatanId());
        } elseif (self::isAdminDesa()) {
            $query->whereKey(self::desaId());
        }

        return $query;
    }
}
