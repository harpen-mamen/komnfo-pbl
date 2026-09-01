<?php

namespace App\Observers;

use App\Models\MobileSyncOperation;
use App\Models\SubmissionBatch;

/**
 * Sync SubmissionBatch status changes back to MobileSyncOperation.
 * This ensures mobile drafts show the correct verification status
 * when AdminKecamatan reviews/approves mobile-submitted batches.
 */
class SubmissionBatchObserver
{
    public function updated(SubmissionBatch $batch): void
    {
        if (! $batch->isDirty('status')) {
            return;
        }

        $oldStatus = $batch->getOriginal('status');
        $newStatus = $batch->status;

        // Only react to status transitions that affect mobile users
        if (! in_array($newStatus, [
            SubmissionBatch::STATUS_APPROVED,
            SubmissionBatch::STATUS_REJECTED,
            SubmissionBatch::STATUS_IN_REVIEW,
            SubmissionBatch::STATUS_REVISION,
        ], true)) {
            return;
        }

        // Map batch status → MobileSyncOperation status
        $operationStatus = match ($newStatus) {
            SubmissionBatch::STATUS_APPROVED => 'accepted',
            SubmissionBatch::STATUS_REJECTED => 'rejected',
            SubmissionBatch::STATUS_IN_REVIEW => 'in_review',
            SubmissionBatch::STATUS_REVISION => 'needs_revision',
            default => null,
        };

        if ($operationStatus === null) {
            return;
        }

        MobileSyncOperation::query()
            ->where('batch_id', $batch->id)
            ->where('status', 'pending')
            ->update(['status' => $operationStatus]);
    }
}
