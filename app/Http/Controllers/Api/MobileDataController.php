<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\KeluargaResource;
use App\Http\Resources\PendudukResource;
use App\Http\Resources\RumahResource;
use App\Http\Resources\SubmissionBatchResource;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SPRINT 6: Mobile Data Controller
 *
 * Controller untuk API mobile yang menyediakan data untuk Flutter app Admin Desa.
 * READ MODEL: Semua query menggunakan inApprovedBatch() (Arsitektur §4.3)
 */
class MobileDataController
{
    /**
     * Get summary data untuk dashboard mobile.
     */
    public function dashboard(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;
        $kecamatanId = $request->user()->kecamatan_id ?? $request->user()->desa?->parent_id;

        // READ MODEL: Only approved data (Arsitektur §4.3)
        $rumahCount = Rumah::query()->inApprovedBatch()->where('desa_id', $desaId)->count();
        $keluargaCount = Keluarga::query()->inApprovedBatch()->whereHas('rumah', fn ($q) => $q->where('desa_id', $desaId))->count();
        $pendudukCount = Penduduk::query()->inApprovedBatch()->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desaId))->count();

        $pendingBatches = SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->whereIn('status', [
                SubmissionBatch::STATUS_PENDING,
                SubmissionBatch::STATUS_IN_REVIEW,
            ])
            ->where('created_by', $request->user()->id)
            ->with('kecamatan')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'rumah_count' => $rumahCount,
                'keluarga_count' => $keluargaCount,
                'penduduk_count' => $pendudukCount,
                'pending_batches' => SubmissionBatchResource::collection($pendingBatches),
            ],
        ]);
    }

    /**
     * Get list rumah dengan pagination.
     */
    public function rumah(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;
        $perPage = (int) $request->get('per_page', 20);

        $rumah = Rumah::query()
            ->inApprovedBatch()
            ->where('desa_id', $desaId)
            ->with(['desa', 'keluargas'])
            ->orderBy('kode_rumah')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => RumahResource::collection($rumah),
            'meta' => [
                'current_page' => $rumah->currentPage(),
                'last_page' => $rumah->lastPage(),
                'per_page' => $rumah->perPage(),
                'total' => $rumah->total(),
            ],
        ]);
    }

    /**
     * Get detail rumah.
     */
    public function rumahDetail(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;

        $rumah = Rumah::query()
            ->inApprovedBatch()
            ->where('id', $id)
            ->where('desa_id', $desaId)
            ->with(['desa', 'keluargas.penduduks'])
            ->first();

        if (!$rumah) {
            return response()->json(['success' => false, 'message' => 'Rumah tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RumahResource($rumah),
        ]);
    }

    /**
     * Get list keluarga dengan pagination.
     */
    public function keluarga(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;
        $perPage = (int) $request->get('per_page', 20);

        $keluarga = Keluarga::query()
            ->inApprovedBatch()
            ->whereHas('rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->with(['rumah.desa', 'penduduks'])
            ->orderBy('nomor_kk')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => KeluargaResource::collection($keluarga),
            'meta' => [
                'current_page' => $keluarga->currentPage(),
                'last_page' => $keluarga->lastPage(),
                'per_page' => $keluarga->perPage(),
                'total' => $keluarga->total(),
            ],
        ]);
    }

    /**
     * Get detail keluarga.
     */
    public function keluargaDetail(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;

        $keluarga = Keluarga::query()
            ->inApprovedBatch()
            ->where('id', $id)
            ->whereHas('rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->with(['rumah.desa', 'penduduks'])
            ->first();

        if (!$keluarga) {
            return response()->json(['success' => false, 'message' => 'Kartu Keluarga tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new KeluargaResource($keluarga),
        ]);
    }

    /**
     * Get list penduduk dengan pagination.
     */
    public function penduduk(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;
        $perPage = (int) $request->get('per_page', 20);
        $search = $request->get('search');

        $penduduk = Penduduk::query()
            ->inApprovedBatch()
            ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->with(['keluarga.rumah.desa'])
            ->when($search, fn ($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
            ->orderBy('nama_lengkap')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PendudukResource::collection($penduduk),
            'meta' => [
                'current_page' => $penduduk->currentPage(),
                'last_page' => $penduduk->lastPage(),
                'per_page' => $penduduk->perPage(),
                'total' => $penduduk->total(),
            ],
        ]);
    }

    /**
     * Get detail penduduk.
     */
    public function pendudukDetail(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;

        $penduduk = Penduduk::query()
            ->inApprovedBatch()
            ->where('id', $id)
            ->whereHas('keluarga.rumah', fn ($q) => $q->where('desa_id', $desaId))
            ->with(['keluarga.rumah.desa'])
            ->first();

        if (!$penduduk) {
            return response()->json(['success' => false, 'message' => 'Penduduk tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PendudukResource($penduduk),
        ]);
    }

    /**
     * Get submission batches untuk mobile.
     */
    public function batches(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $kecamatanId = $request->user()->kecamatan_id ?? $request->user()->desa?->parent_id;
        $perPage = (int) $request->get('per_page', 20);
        $status = $request->get('status');

        $batches = SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->where('created_by', $request->user()->id)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['kecamatan', 'items'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SubmissionBatchResource::collection($batches),
            'meta' => [
                'current_page' => $batches->currentPage(),
                'last_page' => $batches->lastPage(),
                'per_page' => $batches->perPage(),
                'total' => $batches->total(),
            ],
        ]);
    }

    /**
     * Sync data - get latest approved data.
     */
    public function sync(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        $desaId = (int) $request->user()->desa_id;
        $lastSync = $request->get('last_sync');

        $rumah = Rumah::query()
            ->inApprovedBatch()
            ->where('desa_id', $desaId)
            ->when($lastSync, fn ($q) => $q->where('updated_at', '>', $lastSync))
            ->with(['desa', 'keluargas.penduduks'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'rumah' => RumahResource::collection($rumah),
                'sync_time' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Reference data untuk form mobile (status options, enum values).
     */
    public function reference(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdminDesa() && $request->user()->desa_id, 403, 'Akses ditolak.');

        return response()->json([
            'success' => true,
            'data' => [
                'status_rumah' => array_keys(Rumah::STATUS),
                'status_ekonomi' => array_keys(Keluarga::STATUS_EKONOMI),
                'jenis_kelamin' => array_keys(Penduduk::JENIS_KELAMIN),
                'agama' => array_keys(Penduduk::AGAMA),
                'status_perkawinan' => array_keys(Penduduk::STATUS_PERKAWINAN),
                'pendidikan' => array_keys(Penduduk::PENDIDIKAN),
                'status_dalam_keluarga' => array_keys(Penduduk::STATUS_DALAM_KELUARGA),
                'status_penduduk' => array_keys(Penduduk::STATUS_PENDUDUK),
            ],
        ]);
    }
}

