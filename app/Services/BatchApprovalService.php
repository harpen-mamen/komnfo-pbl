<?php

namespace App\Services;

use App\Enums\AppRole;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Services\Notification\BatchNotificationDispatcher;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 5: Facade over the WorkflowEngine + BatchNotificationDispatcher.
 *
 * SIPANDU Workflow Architecture (enforced):
 * ==========================================
 *
 *  Admin Desa  → submit()      → PENDING   → notify Admin Kecamatan
 *  Admin Kecamatan → startReview() → IN_REVIEW
 *     ├─ reject()          → REJECTED  → notify creator
 *     ├─ requestRevision() → REVISION  → notify creator
 *     └─ forwardToKominfo() → IN_REVIEW (forwarded_at set) → notify Kominfo
 *
 *  Admin Kominfo (ONLY) → approve()   → APPROVED + publish → notify all
 *  Admin Kominfo       → reject()    → REJECTED           → notify creator
 *  Admin Kominfo       → requestRevision() → REVISION      → notify creator
 *
 * CRITICAL RULE: Admin Kecamatan CANNOT final-approve master-data batches.
 *   They can ONLY: startReview, reject, requestRevision, forwardToKominfo.
 *   Kominfo is the mandatory final gate for rumah/keluarga/penduduk.
 */
class BatchApprovalService
{
    public function __construct(
        private readonly WorkflowEngine $engine,
        private readonly BatchNotificationDispatcher $notifier,
    ) {}

    /**
     * Submit batch - oleh Admin Desa atau Admin Kecamatan.
     * Status: DRAFT → PENDING
     */
    public function submit(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null, bool $notify = true): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_PENDING,
            $actor,
            $catatan,
            $notify ? 'submit' : null,
        );
    }

    /**
     * Mulai review - oleh Admin Kecamatan.
     * Status: PENDING → IN_REVIEW
     */
    public function startReview(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_IN_REVIEW,
            $actor,
            $catatan,
            notificationType: null,
        );
    }

    /**
     * FINAL APPROVE — only Admin Kominfo (or kabupaten admins).
     * Status: IN_REVIEW → APPROVED (triggers publish via afterCommit in WorkflowEngine)
     *
     * CRITICAL FIX C2: Admin Kecamatan CANNOT call this for master-data batches.
     *   The WorkflowEngine enforces this, but this docblock makes it explicit.
     */
    public function approve(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null, bool $notify = true): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_APPROVED,
            $actor,
            $catatan,
            $notify ? 'approve' : null,
        );
    }

    /**
     * REJECT oleh Admin Kecamatan ATAU Admin Kominfo.
     * Status: IN_REVIEW → REJECTED
     */
    public function reject(SubmissionBatch $batch, ?User $actor = null, ?string $alasan = null, bool $notify = true): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_REJECTED,
            $actor,
            $alasan,
            $notify ? 'reject' : null,
        );
    }

    /**
     * Request Revision - oleh Admin Kecamatan ATAU Admin Kominfo.
     * Status: IN_REVIEW → REVISION
     */
    public function requestRevision(SubmissionBatch $batch, ?User $actor = null, ?string $alasan = null, bool $notify = true): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_REVISION,
            $actor,
            $alasan,
            $notify ? 'revision' : null,
        );
    }

    /**
     * Archive batch - hanya Admin Kominfo.
     * Status: APPROVED/REJECTED → ARCHIVED
     */
    public function archive(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null, bool $notify = true): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_ARCHIVED,
            $actor,
            $catatan,
            $notify ? 'archive' : null,
        );
    }

    /**
     * Forward batch from Admin Kecamatan to Admin Kominfo.
     * Status: IN_REVIEW → IN_REVIEW (status unchanged, but forwarded_at/forwarded_by set)
     *
     * CRITICAL FIX: Forward now sets explicit forwarded_at/forwarded_by columns.
     *   Previously used `catatan LIKE '%forward%'` — fragile and unreliable.
     *   Now uses a proper timestamp + foreign key on submission_batches.
     */
    public function forwardToKominfo(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_IN_REVIEW,
            $actor,
            $catatan,
            notificationType: 'forward_to_kominfo',
        );
    }

    /**
     * Publish batch yang sudah approved.
     */
    public function publish(SubmissionBatch $batch, ?User $actor = null): array
    {
        return $this->engine->publish($batch, $actor);
    }

    /**
     * Submit batch for review (draft → in_review).
     * Used by puskes/sekolah operators to submit their data for review.
     */
    public function submitForReview(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null, bool $notify = true): SubmissionBatch
    {
        return $this->engine->transition(
            $batch,
            SubmissionBatch::STATUS_IN_REVIEW,
            $actor,
            $catatan,
            $notify ? $this->notificationTypeForModule($batch->module) : null,
        );
    }

    /**
     * Check if a user can approve this batch.
     *
     * CRITICAL FIX C2:
     *   Before: Admin Kecamatan could approve master-data batches in their wilayah.
     *   After:  Only Admin Kominfo (or kabupaten admins) can approve.
     *           Admin Kecamatan is limited to: startReview, reject, requestRevision, forward.
     *
     * @param  User  $user
     * @param  SubmissionBatch  $batch
     * @return bool
     */
    public function canApprove(User $user, SubmissionBatch $batch): bool
    {
        $module = $batch->module;

        // Kabupaten level: can approve all batches
        if ($user->hasAnyRole(['super_admin', 'admin', 'admin_kominfo'])) {
            // For master-data batches, also require that the batch was forwarded to Kominfo.
            // (defense-in-depth; the WorkflowEngine also enforces this)
            if ($batch->isMasterData() && ! $batch->isForwarded()) {
                return false;
            }

            return true;
        }

        // Admin Kecamatan CANNOT approve master data — they can only forward.
        // OPD module approvals are handled by OPD admins.
        if ($user->hasRole('admin_kecamatan')) {
            return false;
        }

        // OPD module approvals — oleh OPD admin terkait
        if (in_array($module, [SubmissionBatch::MODULE_KESEHATAN, SubmissionBatch::MODULE_PUSKES], true)) {
            return $user->hasAnyRole([
                AppRole::ADMIN_KESEHATAN->value,
                AppRole::ADMIN_DINKES->value,
                AppRole::ADMIN_PUSKESMAS->value,
            ]);
        }

        if (in_array($module, [SubmissionBatch::MODULE_PENDIDIKAN, SubmissionBatch::MODULE_SEKOLAH], true)) {
            return $user->hasAnyRole([
                AppRole::ADMIN_PENDIDIKAN->value,
                AppRole::ADMIN_DISPEND->value,
                AppRole::ADMIN_SEKOLAH->value,
            ]);
        }

        return false;
    }

    /**
     * Check if a user can START REVIEW (PENDING → IN_REVIEW) this batch.
     * Admin Kecamatan AND Admin Kominfo can both start review.
     */
    public function canStartReview(User $user, SubmissionBatch $batch): bool
    {
        if ($batch->status !== SubmissionBatch::STATUS_PENDING) {
            return false;
        }

        // Admin Kominfo can start review on any batch
        if ($user->hasAnyRole(['super_admin', 'admin', 'admin_kominfo'])) {
            return true;
        }

        // Admin Kecamatan can start review on batches in their wilayah
        if ($user->hasRole('admin_kecamatan')) {
            $userKecamatanId = $user->kecamatan_id ?? $user->desa?->kecamatan_id;
            return (int) $userKecamatanId === (int) $batch->kecamatan_id;
        }

        return false;
    }

    /**
     * Check if user can forward batch to Kominfo.
     * Only Admin Kecamatan can forward batches in their wilayah.
     */
    public function canForwardToKominfo(User $user, SubmissionBatch $batch): bool
    {
        // Already forwarded — no need to forward again
        if ($batch->isForwarded()) {
            return false;
        }

        if ($batch->status !== SubmissionBatch::STATUS_IN_REVIEW) {
            return false;
        }

        if ($user->hasRole('admin_kecamatan')) {
            $userKecamatanId = $user->kecamatan_id ?? $user->desa?->kecamatan_id;
            return (int) $userKecamatanId === (int) $batch->kecamatan_id;
        }

        return false;
    }

    /**
     * Whether a master-data batch from Admin Desa has passed the Kecamatan gate
     * and is ready for Kominfo final verification.
     *
     * CRITICAL FIX: Replaced `catatan LIKE '%forward%'` with explicit forwarded_at check.
     *
     * @param  SubmissionBatch  $batch
     * @return bool  true if Kominfo can now review/approve this batch
     */
    public function isReadyForKominfoVerification(SubmissionBatch $batch): bool
    {
        // Batches created directly by Admin Kecamatan (not from Admin Desa) skip forward.
        if (! $this->wasCreatedByAdminDesa($batch)) {
            return true;
        }

        // Master-data batches from Admin Desa require explicit forward.
        // OPD batches follow their own approval path.
        if ($batch->isMasterData()) {
            return $batch->isForwarded();
        }

        return false;
    }

    /**
     * Check if batch was created by Admin Desa.
     */
    private function wasCreatedByAdminDesa(SubmissionBatch $batch): bool
    {
        $batch->loadMissing('createdBy.roles');

        return $batch->createdBy?->hasRole('admin_desa') ?? false;
    }

    /**
     * Determine notification type based on module.
     */
    private function notificationTypeForModule(string $module): string
    {
        return match ($module) {
            SubmissionBatch::MODULE_PUSKES => 'puskes',
            SubmissionBatch::MODULE_SEKOLAH => 'sekolah',
            default => 'kecamatan',
        };
    }

    /**
     * Build a human-readable timeline of the batch for the UI.
     */
    public function createTimeline(SubmissionBatch $batch): Collection
    {
        return collect([
            [
                'jenis' => 'batch',
                'waktu' => $batch->created_at,
                'status_baru' => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_DRAFT),
                'catatan' => 'Batch dibuat',
                'dilakukan_oleh' => $batch->createdBy?->name,
            ],
            [
                'jenis' => 'batch',
                'waktu' => $batch->submitted_at,
                'status_baru' => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_PENDING),
                'catatan' => 'Batch dikirim untuk diverifikasi',
                'dilakukan_oleh' => $batch->createdBy?->name,
            ],
            [
                'jenis' => 'batch',
                'waktu' => $batch->verified_at,
                'status_baru' => SubmissionBatch::statusLabel($batch->status),
                'catatan' => $batch->notes,
                'dilakukan_oleh' => $batch->verifiedBy?->name,
            ],
        ])->filter(fn (array $item): bool => ! empty($item['waktu']))->values();
    }

    /**
     * Get workflow status ID by kode.
     */
    private function getWorkflowStatusId(string $status): ?int
    {
        return \Illuminate\Support\Facades\DB::table('workflow_status')
            ->where('kode', $status)
            ->value('id');
    }
}
