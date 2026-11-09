<?php

namespace App\Services;

use App\Models\SubmissionBatch;
use Illuminate\Support\Collection;

/**
 * SubmissionVersionService — Versioning & Revision tracking.
 *
 * SERVICE LAYER: Arsitektur §7.1
 * Handles version tracking for submission batches during revision cycles.
 *
 * Responsibilities:
 * - Track revision history across batch submissions
 * - Compare previous vs current snapshot
 * - Determine if a batch is a new version of a previous submission
 * - Calculate version chain for audit trail
 */
class SubmissionVersionService
{
    /**
     * Get all versions of a batch (current + previous through revision chain).
     */
    public function getVersionChain(SubmissionBatch $batch): Collection
    {
        $chain = collect();
        $current = $batch;

        while ($current !== null) {
            $chain->push([
                'id' => $current->id,
                'submission_number' => $current->submission_number,
                'version' => $current->version,
                'status' => $current->status,
                'status_label' => SubmissionBatch::statusLabel($current->status),
                'submitted_at' => $current->submitted_at,
                'verified_at' => $current->verified_at,
                'verified_by' => $current->verifiedBy?->name,
                'notes' => $current->notes,
                'created_by' => $current->createdBy?->name,
                'created_at' => $current->created_at,
            ]);

            // Follow revision chain
            $snapshot = $current->payload_snapshot ?? [];
            $previousId = $snapshot['revision']['previous_submission_batch_id'] ?? null;
            $current = $previousId ? SubmissionBatch::query()->find($previousId) : null;
        }

        return $chain;
    }

    /**
     * Get the previous version in the revision chain.
     */
    public function getPreviousVersion(SubmissionBatch $batch): ?SubmissionBatch
    {
        $snapshot = $batch->payload_snapshot ?? [];
        $previousId = $snapshot['revision']['previous_submission_batch_id'] ?? null;

        if (! $previousId) {
            return null;
        }

        return SubmissionBatch::query()->find($previousId);
    }

    /**
     * Calculate what changed between current and previous batch snapshot.
     */
    public function diffWithPrevious(SubmissionBatch $batch): array
    {
        $previous = $this->getPreviousVersion($batch);

        if (! $previous) {
            return [
                'is_new' => true,
                'previous_id' => null,
                'changes' => [],
            ];
        }

        $currentSnapshot = $batch->payload_snapshot ?? [];
        $previousSnapshot = $previous->payload_snapshot ?? [];

        $changes = [];

        // Compare module counts
        $currentModules = $currentSnapshot['modules'] ?? [];
        $previousModules = $previousSnapshot['modules'] ?? [];

        foreach ($currentModules as $module) {
            $moduleKey = $module['module'] ?? 'unknown';
            $previousModule = collect($previousModules)->firstWhere('module', $moduleKey);

            if ($previousModule) {
                $countDiff = ($module['count'] ?? 0) - ($previousModule['count'] ?? 0);
                if ($countDiff !== 0) {
                    $changes[$moduleKey] = [
                        'type' => 'count_change',
                        'previous' => $previousModule['count'] ?? 0,
                        'current' => $module['count'] ?? 0,
                        'diff' => $countDiff,
                    ];
                }
            } else {
                $changes[$moduleKey] = [
                    'type' => 'new_module',
                    'current' => $module['count'] ?? 0,
                ];
            }
        }

        // Find removed modules
        foreach ($previousModules as $module) {
            $moduleKey = $module['module'] ?? 'unknown';
            $currentModule = collect($currentModules)->firstWhere('module', $moduleKey);
            if (! $currentModule) {
                $changes[$moduleKey] = [
                    'type' => 'removed_module',
                    'previous' => $module['count'] ?? 0,
                ];
            }
        }

        return [
            'is_new' => false,
            'previous_id' => $previous->id,
            'previous_submission_number' => $previous->submission_number,
            'previous_status' => $previous->status,
            'version' => $currentSnapshot['revision']['number'] ?? 1,
            'changes' => $changes,
        ];
    }

    /**
     * Get revision count for a batch (how many times it was resubmitted).
     */
    public function getRevisionCount(SubmissionBatch $batch): int
    {
        return $this->getVersionChain($batch)->count() - 1;
    }

    /**
     * Check if a batch is the latest version in its revision chain.
     */
    public function isLatestVersion(SubmissionBatch $batch): bool
    {
        $snapshot = $batch->payload_snapshot ?? [];
        $currentVersionId = $snapshot['revision']['previous_submission_batch_id'] ?? null;

        // If there's no previous version, this is the first (and latest)
        if ($currentVersionId === null) {
            return true;
        }

        // Check if any batch in the chain references this batch as its previous
        return ! SubmissionBatch::query()
            ->whereRaw("JSON_EXTRACT(payload_snapshot, '\$.revision.previous_submission_batch_id') = ?", [$batch->id])
            ->exists();
    }

    /**
     * Build version label for display (e.g., "Revisi 3").
     */
    public function versionLabel(SubmissionBatch $batch): string
    {
        $snapshot = $batch->payload_snapshot ?? [];
        $versionNumber = $snapshot['revision']['number'] ?? 1;

        if ($versionNumber === 1) {
            return 'Versi 1 (Awal)';
        }

        return "Revisi {$versionNumber}";
    }
}
