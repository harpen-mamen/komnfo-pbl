<?php

namespace App\Services\Submission;

use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 5: Extracted from SubmissionBatchService — handles batch creation logic.
 *
 * Responsibilities:
 * - Create new SubmissionBatch records
 * - Create associated SubmissionBatchItem entries
 * - Assign submission_batch_id to OPD records (kesehatan, pendidikan)
 * - Generate unique submission_number
 */
class SubmissionBatchCreator
{
    /**
     * Create batch row with all items in a single transaction.
     *
     * @param  array  $modulePayloads  Array of {module, entity_type, ids[]} entries
     */
    public function create(
        int $kecamatanId,
        array $modulePayloads,
        ?int $periodeBulan,
        ?string $semester,
        ?int $periodeTahun,
        string $module,
        ?string $notes,
        User $actor,
    ): SubmissionBatch {
        $batch = SubmissionBatch::create([
            'submission_number' => $this->generateSubmissionNumber($kecamatanId, $module, $periodeBulan, $periodeTahun),
            'module' => $module,
            'kecamatan_id' => $kecamatanId,
            'periode_bulan' => $periodeBulan,
            'semester' => $semester,
            'periode_tahun' => $periodeTahun,
            'status' => SubmissionBatch::STATUS_DRAFT,
            'notes' => $notes,
            'created_by' => $actor->id,
            'package_keys' => array_keys($modulePayloads),
            'package_summary' => $this->buildPackageSummary($modulePayloads),
            'package_signature' => $this->buildPackageSignature($modulePayloads),
            'version' => 0,
        ]);

        $this->createBatchItems($batch, $modulePayloads);
        $this->assignOpdRecordsToBatch($modulePayloads, $batch->id);

        return $batch->fresh();
    }

    /**
     * Create submission_batch_items rows for all referenced entities.
     */
    public function createBatchItems(SubmissionBatch $batch, array $modulePayloads): void
    {
        $rows = [];

        foreach ($modulePayloads as $payload) {
            if (! isset($payload['entity_type'], $payload['ids'])) {
                continue;
            }

            $now = now();
            foreach ($payload['ids'] as $entityId) {
                $rows[] = [
                    'submission_batch_id' => $batch->id,
                    'package_key' => $payload['module'],
                    'entity_type' => $payload['entity_type'],
                    'entity_id' => $entityId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows === []) {
            return;
        }

        // Use upsert to handle duplicates gracefully
        SubmissionBatchItem::upsert(
            $rows,
            ['submission_batch_id', 'entity_type', 'entity_id'],
            ['updated_at'],
        );
    }

    /**
     * Assign submission_batch_id to OPD records (kesehatan, pendidikan).
     */
    public function assignOpdRecordsToBatch(array $modulePayloads, int $batchId): void
    {
        foreach ($modulePayloads as $payload) {
            if (! in_array($payload['module'], ['kesehatan', 'pendidikan'], true)) {
                continue;
            }

            $modelClass = $payload['entity_type'] ?? null;
            if (! $modelClass || ! class_exists($modelClass)) {
                continue;
            }

            $modelClass::query()
                ->whereKey($payload['ids'])
                ->update(['submission_batch_id' => $batchId]);
        }
    }

    /**
     * Generate unique submission number: SB-{MODULE}-KEC-{id}-B{bulan}-T{tahun}-{seq}
     */
    public function generateSubmissionNumber(int $kecamatanId, string $module, ?int $periodeBulan, ?int $periodeTahun): string
    {
        $year = $periodeTahun ?? now()->year;
        $bulan = $periodeBulan ? str_pad((string) $periodeBulan, 2, '0', STR_PAD_LEFT) : 'XX';

        $lastSeq = SubmissionBatch::query()
            ->where('module', $module)
            ->where('kecamatan_id', $kecamatanId)
            ->where('periode_bulan', $periodeBulan)
            ->where('periode_tahun', $year)
            ->lockForUpdate()
            ->count();

        $seq = str_pad((string) ($lastSeq + 1), 3, '0', STR_PAD_LEFT);

        return sprintf('SB-%s-KEC-%d-B%s-T%d-%s', strtoupper($module), $kecamatanId, $bulan, $year, $seq);
    }

    private function buildPackageSummary(array $modulePayloads): array
    {
        return collect($modulePayloads)
            ->mapWithKeys(fn (array $payload) => [
                $payload['module'] => [
                    'count' => count($payload['ids'] ?? []),
                    'entity_type' => $payload['entity_type'] ?? null,
                ],
            ])
            ->all();
    }

    private function buildPackageSignature(array $modulePayloads): string
    {
        $signatureData = collect($modulePayloads)
            ->map(fn (array $payload) => [
                'module' => $payload['module'],
                'ids' => $payload['ids'] ?? [],
            ])
            ->sortBy('module')
            ->values()
            ->all();

        return hash('sha256', json_encode($signatureData));
    }
}
