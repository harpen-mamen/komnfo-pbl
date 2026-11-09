<?php

namespace App\Http\Controllers\Api;

use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\MobileFieldPoint;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\Wilayah;
use App\Services\BatchApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileFieldController
{
    private const POINT_TYPES = ['rumah', 'jalan', 'jembatan', 'drainase', 'lampu_jalan', 'air_bersih', 'lainnya'];

    /** OPD datasets can opt into this picker with config.mobile_family_source=true. */
    public function families(Request $request): JsonResponse
    {
        $user = $this->fieldUser($request);
        $data = $request->validate(['desa_id' => ['required', 'integer']]);
        $desaId = (int) $data['desa_id'];
        abort_unless($this->allowedVillageQuery($user)->whereKey($desaId)->exists(), 403, 'Desa bukan wilayah tugas akun ini.');
        $datasets = Dataset::query()->where('is_active', true)->where('cakupan_wilayah', 'desa')->get()
            ->filter(fn (Dataset $dataset): bool => (bool) data_get($dataset->config, 'mobile_family_source'));

        if ($datasets->isEmpty()) {
            return response()->json([
                'data' => [],
                'message' => 'Belum ada dataset keluarga OPD aktif dengan cakupan per desa.',
            ]);
        }

        $sourceDatasetIds = $datasets->modelKeys();
        $query = DatasetValue::query()
            ->whereIn('dataset_id', $sourceDatasetIds)
            ->where(function ($query): void {
                $query->where('wilayah_type', DatasetValue::WILAYAH_DESA)
                    ->orWhere('wilayah_type', Wilayah::class);
            })
            ->where('wilayah_id', $desaId)
            ->where('status', DatasetValue::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->orderByDesc('id');

        if ($term = trim((string) $request->query('q', ''))) {
            $query->where('values', 'like', '%'.$term.'%');
        }

        $rows = $query->limit(100)->get()->map(function (DatasetValue $value): array {
            $data = $value->values ?? [];
            return [
                'id' => $value->id,
                'dataset_id' => $value->dataset_id,
                'nomor_kk' => data_get($data, 'nomor_kk', data_get($data, 'no_kk', '')),
                'nama_kepala_keluarga' => data_get($data, 'nama_kepala_keluarga', data_get($data, 'kepala_keluarga', data_get($data, 'nama', ''))),
                'jumlah_anggota' => data_get($data, 'jumlah_anggota'),
            ];
        })->filter(fn (array $row): bool => $row['nomor_kk'] !== '' || $row['nama_kepala_keluarga'] !== '')->values();

        return response()->json(['desa_id' => $desaId, 'data' => $rows]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->fieldUser($request);
        $rows = MobileFieldPoint::query()->whereIn('desa_id', $this->allowedVillageQuery($user)->select('id'))->latest()->limit(500)->get()
            ->map(function (MobileFieldPoint $point): array {
                $row = $point->toArray();
                $row['is_verified'] = in_array($point->submissionBatch?->status, [SubmissionBatch::STATUS_APPROVED], true);
                $row['batch_status'] = $point->submissionBatch?->status;
                return $row;
            });
        return response()->json(['data' => $rows]);
    }

    public function batchStatuses(Request $request): JsonResponse
    {
        $user = $this->fieldUser($request);
        $ids = collect(explode(',', (string) $request->query('ids', '')))->map(fn ($id) => (int) $id)->filter()->unique();
        $batches = SubmissionBatch::query()
            ->whereIn('id', $ids)
            ->whereHas('items', fn ($query) => $query->where('entity_type', MobileFieldPoint::class)
                ->whereIn('entity_id', MobileFieldPoint::query()
                    ->where('created_by', $user->id)
                    ->whereIn('desa_id', $this->allowedVillageQuery($user)->select('id'))
                    ->select('id')))
            ->get()
            ->map(fn (SubmissionBatch $batch): array => [
                'id' => $batch->id,
                'status' => $batch->status,
                'status_label' => SubmissionBatch::statusLabel($batch->status),
            ]);

        return response()->json(['batches' => $batches]);
    }

    public function push(Request $request): JsonResponse
    {
        $user = $this->fieldUser($request);
        $data = $request->validate([
            'operations' => ['required', 'array', 'max:100'],
            'operations.*.operation_id' => ['required', 'uuid'],
            'operations.*.desa_id' => ['required', 'integer'],
            'operations.*.jenis' => ['required', 'in:'.implode(',', self::POINT_TYPES)],
            'operations.*.nama' => ['required', 'string', 'max:255'],
            'operations.*.kode' => ['nullable', 'string', 'max:120'],
            'operations.*.alamat' => ['nullable', 'string', 'max:2000'],
            'operations.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'operations.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'operations.*.attributes' => ['nullable', 'array'],
            'operations.*.keluarga_ids' => ['nullable', 'array', 'max:100'],
            'operations.*.keluarga_ids.*' => ['integer', 'distinct'],
        ]);

        $results = [];
        foreach ($data['operations'] as $operation) {
            $desaId = (int) $operation['desa_id'];
            abort_unless($this->allowedVillageQuery($user)->whereKey($desaId)->exists(), 403, 'Desa bukan wilayah tugas akun ini.');
            $existing = MobileFieldPoint::query()->where('operation_id', $operation['operation_id'])->first();
            if ($existing) {
                abort_unless((int) $existing->created_by === (int) $user->id && (int) $existing->desa_id === $desaId, 403, 'ID operasi sudah digunakan di luar cakupan akun ini.');
                $results[] = ['operation_id' => $operation['operation_id'], 'status' => 'pending', 'server_id' => $existing->id, 'batch_id' => $existing->submission_batch_id];
                continue;
            }

            $familyIds = collect($operation['keluarga_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
            if ($familyIds->isNotEmpty()) {
                $sourceDatasetIds = Dataset::query()->where('is_active', true)->where('cakupan_wilayah', 'desa')->get()
                    ->filter(fn (Dataset $dataset): bool => (bool) data_get($dataset->config, 'mobile_family_source'))
                    ->modelKeys();
                $valid = DatasetValue::query()
                    ->whereIn('id', $familyIds)
                    ->where(function ($query): void {
                        $query->where('wilayah_type', DatasetValue::WILAYAH_DESA)
                            ->orWhere('wilayah_type', Wilayah::class);
                    })
                    ->where('wilayah_id', $desaId)
                    ->where('status', DatasetValue::STATUS_PUBLISHED)
                    ->whereNotNull('published_at')
                    ->whereIn('dataset_id', $sourceDatasetIds)
                    ->count();
                abort_unless($valid === $familyIds->count(), 422, 'Keluarga tidak ditemukan pada desa tugas Anda.');
            }

            $point = DB::transaction(function () use ($operation, $familyIds, $user, $desaId): MobileFieldPoint {
                $record = MobileFieldPoint::query()->create([
                    'operation_id' => $operation['operation_id'],
                    'desa_id' => $desaId,
                    'jenis' => $operation['jenis'],
                    'kode' => $operation['kode'] ?? null,
                    'nama' => $operation['nama'],
                    'alamat' => $operation['alamat'] ?? null,
                    'latitude' => $operation['latitude'],
                    'longitude' => $operation['longitude'],
                    'attributes' => $operation['attributes'] ?? [],
                    'keluarga_dataset_value_ids' => $familyIds->all(),
                    'created_by' => $user->id,
                ]);

                $kecamatanId = $this->kecamatanId($desaId);
                abort_unless($kecamatanId, 422, 'Desa petugas belum terhubung ke kecamatan.');
                $batch = SubmissionBatch::query()->create([
                    'module' => SubmissionBatch::MODULE_LAPANGAN,
                    'submission_number' => 'SB-LAP-'.$kecamatanId.'-'.Str::upper(Str::random(12)),
                    'kecamatan_id' => $kecamatanId,
                    'package_signature' => hash('sha256', 'mobile-field:'.$record->operation_id),
                    'package_keys' => [SubmissionBatch::MODULE_LAPANGAN],
                    'package_summary' => [SubmissionBatch::MODULE_LAPANGAN => ['count' => 1, 'entity_type' => MobileFieldPoint::class]],
                    'status' => SubmissionBatch::STATUS_DRAFT,
                    'created_by' => $user->id,
                    'payload_snapshot' => ['source' => 'mobile', 'module' => SubmissionBatch::MODULE_LAPANGAN, 'records' => [$record->toArray()]],
                    'version' => 1,
                ]);
                SubmissionBatchItem::query()->create([
                    'submission_batch_id' => $batch->id,
                    'package_key' => SubmissionBatch::MODULE_LAPANGAN,
                    'entity_type' => MobileFieldPoint::class,
                    'entity_id' => $record->id,
                ]);
                $record->update(['submission_batch_id' => $batch->id, 'status' => 'pending']);
                $batch = app(BatchApprovalService::class)->submit($batch, $user);
                $record->update(['status' => $batch->status]);
                return $record->fresh();
            });

            $results[] = ['operation_id' => $operation['operation_id'], 'status' => 'pending', 'server_id' => $point->id, 'batch_id' => $point->submission_batch_id];
        }

        return response()->json(['results' => $results], 201);
    }

    private function fieldUser(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['admin_lapangan', 'admin_desa']), 403, 'Hanya petugas lapangan yang dapat menggunakan endpoint ini.');
        abort_unless($user->desa_id || $user->kecamatan_id, 403, 'Akun petugas belum ditetapkan ke desa atau kecamatan.');
        if ($user->desa_id) {
            abort_unless($this->allowedVillageQuery($user)->whereKey($user->desa_id)->exists(), 403, 'Desa akun tidak sesuai dengan kecamatan tugas.');
        }
        return $user;
    }

    private function allowedVillageQuery($user)
    {
        return Wilayah::query()
            ->where('tipe', Wilayah::TIPE_DESA)
            ->when($user->desa_id, fn ($query) => $query->whereKey($user->desa_id))
            ->when(! $user->desa_id && $user->kecamatan_id, fn ($query) => $query->where('parent_id', $user->kecamatan_id));
    }

    private function kecamatanId(int $desaId): ?int
    {
        $region = Wilayah::query()->find($desaId);
        while ($region) {
            if ($region->tipe === Wilayah::TIPE_KECAMATAN) return (int) $region->id;
            $region = $region->parent;
        }
        return null;
    }
}
