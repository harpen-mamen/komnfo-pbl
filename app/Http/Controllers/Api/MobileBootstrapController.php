<?php

namespace App\Http\Controllers\Api;

use App\Models\MobileFieldPoint;
use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileBootstrapController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles', 'desa');
        abort_unless($user->hasAnyRole(['admin_lapangan', 'admin_desa']) && ($user->desa_id || $user->kecamatan_id), 403, 'Akun petugas harus memiliki cakupan desa atau kecamatan.');

        $desaQuery = Wilayah::query()->where('tipe', Wilayah::TIPE_DESA);
        if ($user->desa_id) {
            $desaQuery->whereKey($user->desa_id);
        } else {
            $desaQuery->where('parent_id', $user->kecamatan_id);
        }
        $desas = $desaQuery->orderBy('nama')->get();
        abort_unless($desas->isNotEmpty(), 403, 'Tidak ada desa yang terhubung dengan akun petugas.');
        $desa = $user->desa_id ? $desas->first() : null;
        $kecamatan = $desa?->parent ?? $user->kecamatan;
        $desaIds = $desas->modelKeys();
        $points = MobileFieldPoint::query()->whereIn('desa_id', $desaIds)->orderBy('id')->get();
        $rumah = $points->where('jenis', 'rumah')->map(fn (MobileFieldPoint $point): array => [
            'id' => $point->id,
            'desa_id' => $point->desa_id,
            'dusun_id' => null,
            'kode_rumah' => $point->kode,
            'alamat_lengkap' => $point->alamat ?? $point->nama,
            'latitude' => $point->latitude,
            'longitude' => $point->longitude,
            'status_rumah' => data_get($point->attributes, 'status_rumah', 'Lainnya'),
            'keluarga_count' => count($point->keluarga_dataset_value_ids ?? []),
            'operation_id' => $point->operation_id,
            'is_verified' => in_array($point->submissionBatch?->status, [\App\Models\SubmissionBatch::STATUS_APPROVED], true),
        ])->values();
        $infrastruktur = $points->where('jenis', '!=', 'rumah')->map(fn (MobileFieldPoint $point): array => [
            'id' => $point->id,
            'operation_id' => $point->operation_id,
            'desa_id' => $point->desa_id,
            'jenis' => $point->jenis,
            'kode' => $point->kode,
            'nama' => $point->nama,
            'alamat' => $point->alamat,
            'latitude' => $point->latitude,
            'longitude' => $point->longitude,
            'is_verified' => in_array($point->submissionBatch?->status, [\App\Models\SubmissionBatch::STATUS_APPROVED], true),
        ])->values();

        return response()->json([
            'version' => '2026.10.28.1',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->values()->all(),
                'role_label' => $user->role_label,
                'kecamatan_id' => $kecamatan?->id,
                'desa_id' => $desa?->id,
            ],
            'datasets' => [
                ['key' => 'rumah', 'label' => 'Rumah Penduduk', 'offline_enabled' => true],
                ['key' => 'infrastruktur', 'label' => 'Infrastruktur', 'offline_enabled' => true],
            ],
            'reference' => [
                'kecamatan' => $kecamatan ? [['id' => $kecamatan->id, 'nama' => $kecamatan->nama]] : [],
                'desa' => $desas->map(fn (Wilayah $village) => ['id' => $village->id, 'nama' => $village->nama, 'kecamatan_id' => $kecamatan?->id])->values(),
                'dusun' => $desas->flatMap(fn (Wilayah $village) => $village->children()->where('tipe', Wilayah::TIPE_DUSUN)->orderBy('nama')->get(['id', 'parent_id', 'nama'])->map(fn (Wilayah $dusun) => [
                    'id' => $dusun->id, 'desa_id' => $village->id, 'nama' => $dusun->nama,
                ]))->values(),
            ],
            'records' => [
                'rumah' => $rumah,
                'keluarga' => [],
                'penduduk' => [],
                'infrastruktur' => $infrastruktur,
            ],
        ]);
    }
}
