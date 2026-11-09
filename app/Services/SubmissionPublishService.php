<?php

namespace App\Services;

use RuntimeException;
use App\Models\Keluarga;
use App\Models\MobileFieldPoint;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubmissionPublishService
{
    /**
     * Publish an approved batch — writes audit_trails records for each entity.
     *
     * CRITICAL FIX C1: Idempotency via is_published flag.
     *   Before: checked only audit_trails existence, which is not atomic with approve.
     *           Race condition: two concurrent approve() calls could both pass the check.
     *   After:  checks is_published on the locked batch row — this flag is set
     *           atomically inside the same DB transaction as the APPROVED status change
     *           (in WorkflowEngine::transition()). The lockForUpdate() here ensures
     *           that even concurrent publish() calls are serialized.
     *
     * @return array<string, mixed>  summary of what was published
     */
    public function publish(SubmissionBatch $batch, ?User $actor = null): array
    {
        $actor ??= Auth::user();

        return DB::transaction(function () use ($batch, $actor): array {
            $lockedBatch = SubmissionBatch::query()
                ->whereKey($batch->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBatch->status !== SubmissionBatch::STATUS_APPROVED) {
                throw new RuntimeException('Batch harus approved sebelum dipublikasikan.');
            }

            // Idempotent guard — if already published, return empty summary.
            // The is_published flag was set atomically inside the same transaction
            // as the APPROVED status change in WorkflowEngine::transition().
            if ($lockedBatch->is_published) {
                return [
                    'submission_batch_id' => $lockedBatch->id,
                    'submission_number' => $lockedBatch->submission_number,
                    'published_at' => DB::table('audit_trails')
                        ->where('entity_type', SubmissionBatch::class)
                        ->where('entity_id', $lockedBatch->id)
                        ->where('field', 'submission_publish')
                        ->where('aksi', 'publish_batch')
                        ->value('created_at'),
                    'already_published' => true,
                    'modules' => [],
                    'total' => 0,
                ];
            }

            $modules = $this->snapshotModules($lockedBatch);
            $published = [];

            foreach ($modules as $moduleSnapshot) {
                $module = $moduleSnapshot['module'] ?? null;
                $entityType = $this->entityTypeForModule($module);
                $records = $moduleSnapshot['records'] ?? null;

                if (! $module || ! $entityType || ! is_array($records)) {
                    throw new RuntimeException('Snapshot publikasi tidak lengkap untuk modul '.($module ?? '-').'.');
                }

                foreach ($records as $record) {
                    $entityId = $record['entity_id'] ?? ($record['attributes']['id'] ?? null);

                    if (! $entityId || ! isset($record['attributes']) || ! is_array($record['attributes'])) {
                        throw new RuntimeException('Snapshot publikasi memiliki record tidak valid.');
                    }

                    DB::table('audit_trails')->insert([
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                        'field' => 'published_payload',
                        'nilai_lama' => null,
                        'nilai_baru' => json_encode([
                            'submission_batch_id' => $lockedBatch->id,
                            'submission_number' => $lockedBatch->submission_number,
                            'module' => $module,
                            'payload' => $record['attributes'],
                        ], JSON_UNESCAPED_UNICODE),
                        'aksi' => 'publish_record',
                        'pengguna_id' => $actor?->id,
                        'pengguna' => $actor?->name,
                        'role' => $actor?->roles?->pluck('name')->first(),
                        'ip' => request()?->ip(),
                        'browser' => request()?->userAgent(),
                        'created_at' => now(),
                    ]);

                    $published[$module] = ($published[$module] ?? 0) + 1;
                }
            }

            $summary = [
                'submission_batch_id' => $lockedBatch->id,
                'submission_number' => $lockedBatch->submission_number,
                'published_at' => now()->toDateTimeString(),
                'published_by' => $actor?->id,
                'modules' => $published,
                'total' => array_sum($published),
            ];

            DB::table('audit_trails')->insert([
                'entity_type' => SubmissionBatch::class,
                'entity_id' => $lockedBatch->id,
                'field' => 'submission_publish',
                'nilai_lama' => null,
                'nilai_baru' => json_encode($summary, JSON_UNESCAPED_UNICODE),
                'aksi' => 'publish_batch',
                'pengguna_id' => $actor?->id,
                'pengguna' => $actor?->name,
                'role' => $actor?->roles?->pluck('name')->first(),
                'ip' => request()?->ip(),
                'browser' => request()?->userAgent(),
                'created_at' => now(),
            ]);

            return $summary;
        });
    }

    private function snapshotModules(SubmissionBatch $batch): array
    {
        $snapshot = $batch->payload_snapshot ?? [];

        if (isset($snapshot['modules']) && is_array($snapshot['modules'])) {
            // SPRINT 6 FIX: Jika snapshot tidak punya records, fetch dari database
            $result = [];
            foreach ($snapshot['modules'] as $moduleSnapshot) {
                $module = $moduleSnapshot['module'] ?? null;
                $entityIds = $moduleSnapshot['entity_ids'] ?? [];
                $records = $moduleSnapshot['records'] ?? null;

                // Skip jika modul tidak valid atau tidak ada data
                if (! $module) {
                    continue;
                }

                // Jika tidak ada records tapi ada entity_ids, fetch dari database
                if (! is_array($records) && ! empty($entityIds)) {
                    $entityType = $this->entityTypeForModule($module);
                    if ($entityType) {
                        $records = $this->fetchRecordsFromDatabase($entityType, $entityIds);
                        $moduleSnapshot['records'] = $records;
                    }
                }

                // Skip modul yang tidak memiliki records setelah proses
                if (! is_array($records) || empty($records)) {
                    continue;
                }

                $result[] = $moduleSnapshot;
            }
            return $result;
        }

        // Fallback untuk format single module
        $module = $snapshot['module'] ?? null;
        $entityIds = $snapshot['entity_ids'] ?? [];
        $records = $snapshot['records'] ?? null;

        if (! $module) {
            return [];
        }

        if (! is_array($records) && ! empty($entityIds)) {
            $entityType = $this->entityTypeForModule($module);
            if ($entityType) {
                $snapshot['records'] = $this->fetchRecordsFromDatabase($entityType, $entityIds);
            }
        }

        return (is_array($records) && ! empty($records)) ? [$snapshot] : [];
    }

    /**
     * SPRINT 6 FIX: Fetch records from database based on entity IDs.
     */
    private function fetchRecordsFromDatabase(string $entityType, array $entityIds): array
    {
        $model = new $entityType();
        $records = $model->whereIn('id', $entityIds)->get();

        return $records->map(fn ($record): array => [
            'entity_id' => $record->id,
            'attributes' => $record->toArray(),
        ])->all();
    }

    private function entityTypeForModule(?string $module): ?string
    {
        return match ($module) {
            SubmissionBatch::MODULE_RUMAH => Rumah::class,
            SubmissionBatch::MODULE_KELUARGA => Keluarga::class,
            SubmissionBatch::MODULE_PENDUDUK => Penduduk::class,
            // DataKesehatanBulanan and DataPendidikanBulanan tables removed - handled by modular models
            SubmissionBatch::MODULE_KESEHATAN => null,
            SubmissionBatch::MODULE_PENDIDIKAN => null,
            SubmissionBatch::MODULE_LAPANGAN => MobileFieldPoint::class,
            default => null,
        };
    }
}
