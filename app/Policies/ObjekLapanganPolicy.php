<?php

namespace App\Policies;

use App\Enums\ObjekJenis;
use App\Models\DATA_LAPANGAN\ObjekLapangan;
use App\Models\User;

/**
 * Policy untuk Objek Lapangan (RUMAH / JALAN / JEMBATAN / DRAINASE / SEKOLAH / KANTOR / MASJID / PUSKESMAS).
 *
 * - ADMIN_DESA: buat & edit objek di unit desanya (mobile offline-first).
 * - ADMIN_KECAMATAN: read-only lintas desa di kecamatannya.
 * - WALIDATA: verifikasi & publikasi.
 * - ADMIN_OPD: hanya bisa lihat objek jenis KANTOR untuk OPD-nya.
 */
class ObjekLapanganPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessFilament();
    }

    public function view(User $user, ObjekLapangan $objek): bool
    {
        if ($user->isPembina() || $user->isWalidata()) {
            return true;
        }
        if ($user->isAdminDesa()) {
            return $objek->unit_id === $user->unit_id;
        }
        if ($user->isAdminKecamatan()) {
            // Scope: kecamatan + desa di bawahnya. Disederhanakan: hanya cek parent chain.
            $kecamatan = $user->unit;
            if (! $kecamatan) {
                return false;
            }
            return $objek->unit_id === $kecamatan->id
                || $objek->unit?->parent_id === $kecamatan->id;
        }
        if ($user->role?->isOpdLevel() === true) {
            // OPD hanya bisa lihat KANTOR di unitnya.
            return $objek->jenis === ObjekJenis::KANTOR && $objek->unit_id === $user->unit_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        // PRODUSEN boleh input: admin_desa (mobile utama), admin_kecamatan (koreksi),
        // WALIDATA (input langsung untuk kasus khusus/pengecualian).
        return $user->canInputObjekLapangan();
    }

    public function update(User $user, ObjekLapangan $objek): bool
    {
        if ($user->isWalidata()) {
            return true;
        }
        if ($user->isAdminDesa()) {
            // Hanya bisa edit objek di unit desanya DAN yang masih draft.
            return $objek->unit_id === $user->unit_id && $objek->status === 'draft';
        }
        return false;
    }

    public function delete(User $user, ObjekLapangan $objek): bool
    {
        return $this->update($user, $objek);
    }

    public function verify(User $user, ObjekLapangan $objek): bool
    {
        return $user->canPublishData() && $objek->status === 'submitted';
    }

    public function publish(User $user, ObjekLapangan $objek): bool
    {
        return $user->canPublishData() && $objek->status === 'verified';
    }
}
