<?php

namespace App\Services;

use RuntimeException;
use App\Models\Keluarga;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FieldLocationSubmissionService
{
    /** Attach already existing Dukcapil records without mutating the source records. */
    public function syncFamilies(Rumah $rumah, array $keluargaIds, User $actor): void
    {
        if (! $actor->isAdminLapangan() || ! $rumah->exists) {
            throw new AuthorizationException('Hanya Admin Lapangan yang dapat menghubungkan data keluarga ke titik rumah.');
        }

        $ids = collect($keluargaIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $families = Keluarga::query()->visibleToCurrentUser()
            ->whereIn('id', $ids)
            ->whereHas('rumah', fn ($query) => $query->where('desa_id', $rumah->desa_id))
            ->get();

        if ($families->count() !== $ids->count()) {
            throw new AuthorizationException('Satu atau beberapa keluarga tidak ditemukan atau berada di luar wilayah tugas Anda.');
        }

        $pivot = $families->mapWithKeys(fn (Keluarga $keluarga): array => [
            $keluarga->id => ['created_by' => $actor->id],
        ])->all();

        $rumah->keluargaRujukan()->sync($pivot);
    }

    /** Create and submit a new spatial point batch to the assigned Kecamatan. */
    public function submitNewPoint(Rumah $rumah, User $actor): SubmissionBatch
    {
        if (! $actor->isAdminLapangan() || ! Rumah::query()->visibleToCurrentUser()->whereKey($rumah->id)->exists()) {
            throw new AuthorizationException('Titik rumah tidak berada dalam wilayah tugas Anda.');
        }

        $kecamatanId = $rumah->desa?->parent_id;
        if (! $kecamatanId) {
            throw new RuntimeException('Desa rumah belum terhubung ke Kecamatan.');
        }

        return DB::transaction(function () use ($rumah, $actor, $kecamatanId): SubmissionBatch {
            $familyIds = $rumah->keluargaRujukan()->pluck('keluarga.id')->map(fn ($id) => (int) $id)->all();
            $batch = SubmissionBatch::query()->create([
                'module' => SubmissionBatch::MODULE_LAPANGAN,
                'submission_number' => 'SB-LAP-'.$kecamatanId.'-'.Str::upper(Str::random(12)),
                'kecamatan_id' => $kecamatanId,
                'package_signature' => hash('sha256', 'lapangan:'.$rumah->id.':'.implode(',', $familyIds)),
                'package_keys' => [SubmissionBatch::MODULE_LAPANGAN],
                'package_summary' => [SubmissionBatch::MODULE_LAPANGAN => [
                    'count' => 1,
                    'entity_type' => Rumah::class,
                    'keluarga_terhubung' => count($familyIds),
                ]],
                'status' => SubmissionBatch::STATUS_DRAFT,
                'created_by' => $actor->id,
                'payload_snapshot' => $this->snapshot($rumah, $familyIds),
                'version' => 0,
            ]);

            SubmissionBatchItem::query()->create([
                'submission_batch_id' => $batch->id,
                'package_key' => SubmissionBatch::MODULE_LAPANGAN,
                'entity_type' => Rumah::class,
                'entity_id' => $rumah->id,
            ]);

            return app(BatchApprovalService::class)->submit($batch, $actor);
        });
    }

    /** Refresh a requested-revision snapshot after the field officer corrects it. */
    public function refreshRevisionSnapshot(Rumah $rumah, User $actor): ?SubmissionBatch
    {
        if (! $actor->isAdminLapangan()) {
            return null;
        }

        $batch = $rumah->submissionBatchItems()
            ->where('package_key', SubmissionBatch::MODULE_LAPANGAN)
            ->with('submissionBatch')
            ->latest('submission_batch_id')
            ->get()
            ->pluck('submissionBatch')
            ->first(fn (?SubmissionBatch $candidate): bool => $candidate?->status === SubmissionBatch::STATUS_REVISION);

        if (! $batch) {
            return null;
        }

        $familyIds = $rumah->keluargaRujukan()->pluck('keluarga.id')->map(fn ($id) => (int) $id)->all();
        $batch->forceFill([
            'payload_snapshot' => $this->snapshot($rumah, $familyIds),
            'package_summary' => [SubmissionBatch::MODULE_LAPANGAN => [
                'count' => 1,
                'entity_type' => Rumah::class,
                'keluarga_terhubung' => count($familyIds),
            ]],
        ])->save();

        return $batch;
    }

    private function snapshot(Rumah $rumah, array $familyIds): array
    {
        $attributes = $rumah->attributesToArray();
        $attributes['keluarga_rujukan_ids'] = $familyIds;

        return [
            'source' => 'web',
            'module' => SubmissionBatch::MODULE_LAPANGAN,
            'entity_ids' => [$rumah->id],
            'records' => [[
                'entity_id' => $rumah->id,
                'attributes' => $attributes,
            ]],
        ];
    }
}
