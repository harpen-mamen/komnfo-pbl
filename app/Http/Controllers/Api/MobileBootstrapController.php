<?php

namespace App\Http\Controllers\Api;

use App\Models\Desa;
use App\Models\Dusun;
use App\Models\Keluarga;
use App\Models\Kecamatan;
use App\Models\Penduduk;
use App\Models\Rumah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileBootstrapController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles', 'desa', 'kecamatan');

        abort_unless($user->isAdminDesa() && $user->desa_id, 403, 'SIPDT Mobile hanya tersedia untuk Admin Desa.');

        $desaId = (int) $user->desa_id;
        $rumah = Rumah::query()->where('desa_id', $desaId)->withCount('keluarga')->orderBy('kode_rumah')->get([
            'id', 'desa_id', 'dusun_id', 'kode_rumah', 'alamat_lengkap', 'latitude', 'longitude', 'status_rumah', 'keterangan', 'updated_at',
        ]);
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $keluarga = Keluarga::query()->inApprovedBatch()->whereHas('rumah', fn ($query) => $query->where('desa_id', $desaId))->orderBy('nomor_kk')->get([
            'id', 'rumah_id', 'nomor_kk', 'nama_kepala_keluarga', 'jumlah_anggota', 'status_ekonomi', 'updated_at',
        ]);
        $penduduk = Penduduk::query()->whereHas('keluarga.rumah', fn ($query) => $query->where('desa_id', $desaId))->orderBy('nama_lengkap')->get([
            'id', 'keluarga_id', 'nik', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'status_perkawinan', 'pendidikan', 'pekerjaan', 'penghasilan', 'status_dalam_keluarga', 'nomor_hp', 'status_penduduk', 'updated_at',
        ]);

        return response()->json([
            'version' => '2026.08.08.1',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->values()->all(),
                'role_label' => $user->role_label,
                'kecamatan_id' => $user->kecamatan_id,
                'desa_id' => $desaId,
            ],
            'datasets' => [
                ['key' => 'rumah', 'label' => 'Rumah', 'offline_enabled' => true],
                ['key' => 'keluarga', 'label' => 'Kartu Keluarga', 'offline_enabled' => true],
                ['key' => 'penduduk', 'label' => 'Penduduk', 'offline_enabled' => true],
            ],
            'reference' => [
                'kecamatan' => Kecamatan::query()->whereKey($user->kecamatan_id)->get(['id', 'nama']),
                'desa' => Desa::query()->whereKey($desaId)->get(['id', 'nama', 'kecamatan_id']),
                'dusun' => Dusun::query()->where('desa_id', $desaId)->orderBy('nama')->get(['id', 'desa_id', 'kode', 'nama']),
            ],
            'records' => [
                'rumah' => $rumah,
                'keluarga' => $keluarga,
                'penduduk' => $penduduk,
            ],
        ]);
    }
}
