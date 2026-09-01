<?php

namespace App\Services\Workflow;

use App\Models\SubmissionBatch;
use App\Models\User;
use App\Services\Notification\BatchNotificationDispatcher;
use App\Services\SubmissionPublishService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 5: State-machine engine for SubmissionBatch.
 *
 * SIPANDU Workflow Architecture (enforced here):
 * ===============================================
 *
 *  Admin Desa  → submit() →  [PENDING]  → notify → Admin Kecamatan
 *  Admin Kecamatan → startReview() → [IN_REVIEW]
 *     ├─ reject()    → [REJECTED] → notify creator
 *     ├─ requestRevision() → [REVISION] → notify creator
 *     └─ forwardToKominfo() → [IN_REVIEW] (forwarded_at set) → notify Kominfo
 *
 *  Admin Kominfo → approve() → [APPROVED] + is_published=1 → notify all
 *  Admin Kominfo → reject()  → [REJECTED]                  → notify creator
 *
 *  Admin Kominfo → requestRevision() → [REVISION] → notify creator
 *
 * CRITICAL RULES (enforced by assertTransitionAllowed):
 *  - Admin Kecamatan CANNOT directly approve master-data batches (rumah/keluarga/penduduk).
 *    They can ONLY forward them to Kominfo for final approval.
 *  - Admin Kominfo is the ONLY role that can final-approve and publish master data.
 *  - OPD batches (kesehatan/pendidikan) follow their own approval path.
 *  - forwardToKominfo() sets forwarded_at/forwarded_by atomically — no string matching.
 *  - Publish is idempotent via is_published flag, set in the same transaction as APPROVED.
 */
class WorkflowEngine
{
    /**
     * Allowed transitions.
     *
     * @var array<string, array<int, string>>
     */
    public const TRANSITIONS = [
        SubmissionBatch::STATUS_DRAFT => [SubmissionBatch::STATUS_PENDING],
        SubmissionBatch::STATUS_PENDING => [SubmissionBatch::STATUS_IN_REVIEW],
        SubmissionBatch::STATUS_IN_REVIEW => [
            // APPROVED is reachable only from Kominfo (enforced in assertTransitionAllowed)
            SubmissionBatch::STATUS_APPROVED,
            SubmissionBatch::STATUS_REJECTED,
            SubmissionBatch::STATUS_REVISION,
        ],
        SubmissionBatch::STATUS_REVISION => [SubmissionBatch::STATUS_PENDING],
        SubmissionBatch::STATUS_APPROVED => [SubmissionBatch::STATUS_ARCHIVED],
        SubmissionBatch::STATUS_REJECTED => [SubmissionBatch::STATUS_ARCHIVED],
        SubmissionBatch::STATUS_ARCHIVED => [],
    ];

    public function __construct(
        private readonly BatchNotificationDispatcher $notifier,
        private readonly SubmissionPublishService $publisher,
    ) {}

    /**
     * Apply a state transition atomically.
     *
     * @param  string  $newStatus  one of SubmissionBatch::STATUS_*
     * @param  string|null  $notificationType  hint for the dispatcher
     */
    public function transition(
        SubmissionBatch $batch,
        string $newStatus,
        ?User $actor = null,
        ?string $notes = null,
        ?string $notificationType = null,
    ): SubmissionBatch {
        $actor ??= Auth::user();
        if (! $actor) {
            throw new AuthorizationException('Pengguna belum terautentikasi.');
        }

        return DB::transaction(function () use ($batch, $newStatus, $actor, $notes, $notificationType) {
            $lockedBatch = SubmissionBatch::query()
                ->whereKey($batch->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $lockedBatch->status;

            // Special case: forward-to-kominfo keeps the batch in IN_REVIEW but
            // bumps the version and notifies Kominfo.
            $isForwardAction = $newStatus === SubmissionBatch::STATUS_IN_REVIEW
                && $oldStatus === SubmissionBatch::STATUS_IN_REVIEW
                && $actor->hasRole('admin_kecamatan');

            if ($isForwardAction) {
                $this->assertForwardAllowed($actor, $lockedBatch);

                $updatedBatch = $this->applyForward($lockedBatch, $actor, $notes);
                $this->recordWorkflowHistory($updatedBatch, $oldStatus, $newStatus, $actor, $notes, isForward: true);
            } else {
                $this->assertTransitionAllowed($actor, $lockedBatch, $newStatus);
                $updatedBatch = $this->applyStatusChange($lockedBatch, $newStatus, $actor, $notes);
                $this->recordWorkflowHistory($updatedBatch, $oldStatus, $newStatus, $actor, $notes);

                // CRITICAL FIX C1: Set is_published flag atomically inside the same
                // transaction as APPROVED. This makes publish idempotent — a second
                // publish() call will find is_published=true and return early without
                // creating duplicate audit_trails records.
                if ($newStatus === SubmissionBatch::STATUS_APPROVED) {
                    $updatedBatch->forceFill(['is_published' => true])->saveQuietly();
                }
            }

            if ($notificationType !== null) {
                DB::afterCommit(function () use ($updatedBatch, $actor, $notificationType, $oldStatus, $newStatus) {
                    $this->dispatchNotification($updatedBatch, $actor, $notificationType, $oldStatus);

                    // CRITICAL FIX C1 (continued): After the APPROVED transaction commits,
                    // trigger the actual publish of audit_trail records. Running it here
                    // (post-commit, outside the lock) avoids holding the row lock during
                    // the potentially slow payload snapshot write. The is_published=true
                    // flag already guarantees idempotency — concurrent publish() calls
                    // are serialized by the lock in SubmissionPublishService::publish().
                    if ($newStatus === SubmissionBatch::STATUS_APPROVED) {
                        $this->publisher->publish($updatedBatch->fresh(), $actor);
                    }
                });
            }

            return $updatedBatch;
        });
    }

    private function assertTransitionAllowed(User $actor, SubmissionBatch $batch, string $newStatus): void
    {
        $allowed = self::TRANSITIONS[$batch->status] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new AuthorizationException(sprintf(
                'Transisi batch dari [%s] ke [%s] tidak diizinkan.',
                $batch->status,
                $newStatus,
            ));
        }

        // Submit: AdminKecamatan/AdminDesa can only submit batches for their own kecamatan.
        if ($newStatus === SubmissionBatch::STATUS_PENDING && $actor->hasAnyRole(['admin_kecamatan', 'admin_desa'])) {
            $actorKecamatanId = $actor->kecamatan_id ?: $actor->desa?->kecamatan_id;
            if ((int) $actorKecamatanId !== (int) $batch->kecamatan_id) {
                throw new AuthorizationException('Admin Kecamatan/Desa tidak dapat mengirim batch kecamatan lain.');
            }

            return;
        }

        // Review: PENDING → IN_REVIEW
        if ($newStatus === SubmissionBatch::STATUS_IN_REVIEW) {
            if ($batch->status === SubmissionBatch::STATUS_PENDING) {
                // Mulai review - Admin Kecamatan atau Admin Kominfo
                if ($actor->hasRole('admin_kecamatan')) {
                    $actorKecamatanId = $actor->kecamatan_id ?: $actor->desa?->kecamatan_id;
                    if ((int) $actorKecamatanId !== (int) $batch->kecamatan_id) {
                        throw new AuthorizationException('Admin Kecamatan hanya dapat mereview batch dari wilayahnya.');
                    }
                    return;
                }

                if ($actor->hasAnyRole(['admin', 'admin_kominfo', 'super_admin'])) {
                    return;
                }

                throw new AuthorizationException('Anda tidak memiliki wewenang untuk memulai review batch.');
            }

            // Forward action - handled separately above
            throw new AuthorizationException(' Aksi tidak valid.');
        }

        // APPROVED/REJECTED — only Kominfo or OPD admins may reach this.
        // CRITICAL FIX C2: Admin Kecamatan CANNOT approve master-data batches directly.
        //   They may ONLY forward them. Kominfo is the mandatory final gate.
        if (in_array($newStatus, [SubmissionBatch::STATUS_APPROVED, SubmissionBatch::STATUS_REJECTED], true)) {
            // Admin Kominfo (and super_admin/admin) can approve/reject all batches
            if ($actor->hasAnyRole(['admin', 'admin_kominfo', 'super_admin'])) {
                // For master-data batches, also require that the batch was forwarded to Kominfo
                if ($newStatus === SubmissionBatch::STATUS_APPROVED && $batch->isMasterData() && ! $batch->isForwarded()) {
                    throw new AuthorizationException(
                        'Batch data master harus diteruskan ke Kominfo terlebih dahulu sebelum dapat disetujui.'
                    );
                }

                return;
            }

            // Admin Kecamatan can reject/request-revision but CANNOT approve master data.
            // They can reject/request-revision for batches in their wilayah.
            if ($actor->hasRole('admin_kecamatan')) {
                $actorKecamatanId = $actor->kecamatan_id ?: $actor->desa?->kecamatan_id;
                if ((int) $actorKecamatanId !== (int) $batch->kecamatan_id) {
                    throw new AuthorizationException('Admin Kecamatan hanya dapat memproses batch dari wilayahnya.');
                }

                // For master data, Admin Kecamatan can ONLY forward — not approve.
                if ($newStatus === SubmissionBatch::STATUS_APPROVED && $batch->isMasterData()) {
                    throw new AuthorizationException(
                        'Admin Kecamatan tidak dapat menyetujui batch data master secara langsung. '
                        .'Teruskan batch ke Kominfo terlebih dahulu.'
                    );
                }

                // Admin Kecamatan can reject/request-revision for any batch in their wilayah.
                return;
            }

            // OPD Admin can approve their own OPD batches
            if ($this->canOpdApprove($actor, $batch)) {
                return;
            }

            throw new AuthorizationException('Anda tidak memiliki wewenang untuk memproses batch ini.');
        }

        // Revision: IN_REVIEW → REVISION
        if ($newStatus === SubmissionBatch::STATUS_REVISION) {
            // Admin Kecamatan bisa minta revisi untuk batch di wilayahnya
            if ($actor->hasRole('admin_kecamatan')) {
                $actorKecamatanId = $actor->kecamatan_id ?: $actor->desa?->kecamatan_id;
                if ((int) $actorKecamatanId === (int) $batch->kecamatan_id) {
                    return;
                }
                throw new AuthorizationException('Admin Kecamatan hanya dapat meminta revisi untuk batch dari wilayahnya.');
            }

            // Admin Kominfo bisa minta revisi
            if ($actor->hasAnyRole(['admin', 'admin_kominfo', 'super_admin'])) {
                return;
            }

            throw new AuthorizationException('Anda tidak memiliki wewenang untuk meminta revisi batch ini.');
        }

        // Archive: APPROVED/REJECTED → ARCHIVED - hanya Admin Kominfo
        if ($newStatus === SubmissionBatch::STATUS_ARCHIVED) {
            if (! $actor->hasAnyRole(['admin', 'admin_kominfo', 'super_admin'])) {
                throw new AuthorizationException('Hanya Admin Kominfo yang dapat mengarsipkan batch.');
            }
            return;
        }

        throw new AuthorizationException('Transisi batch tidak diizinkan untuk role ini.');
    }

    /**
     * Check if OPD user can approve this batch.
     */
    private function canOpdApprove(User $actor, SubmissionBatch $batch): bool
    {
        $module = $batch->module;

        if (in_array($module, [SubmissionBatch::MODULE_KESEHATAN, SubmissionBatch::MODULE_PUSKES], true)) {
            return $actor->hasAnyRole([
                'admin_kesehatan', 'admin_dinkes', 'admin_puskesmas',
            ]);
        }

        if (in_array($module, [SubmissionBatch::MODULE_PENDIDIKAN, SubmissionBatch::MODULE_SEKOLAH], true)) {
            return $actor->hasAnyRole([
                'admin_pendidikan', 'admin_dispend', 'admin_sekolah',
            ]);
        }

        return false;
    }

    /**
     * SIPANDU FIX: Check if batch is ready for Kominfo final verification.
     *
     * A master-data batch from Admin Desa is ready for Kominfo iff:
     *   1. It was NOT created by Admin Desa (Admin Kecamatan's own batches are auto-ready), OR
     *   2. It has been forwarded by Admin Kecamatan (forwarded_at IS NOT NULL).
     *
     * Before: used `catatan LIKE '%forward%'` — fragile string matching.
     * After:  uses explicit `forwarded_at` column — reliable and indexed.
     */
    public function isReadyForKominfoVerification(SubmissionBatch $batch): bool
    {
        // Admin Kecamatan's own batches (not from Admin Desa) skip forward requirement.
        if (! $this->wasCreatedByAdminDesa($batch)) {
            return true;
        }

        // Master-data batch from Admin Desa requires explicit forward.
        // OPD batches follow their own approval path.
        if ($batch->isMasterData()) {
            return $batch->isForwarded();
        }

        return false;
    }

    private function wasCreatedByAdminDesa(SubmissionBatch $batch): bool
    {
        $batch->loadMissing('createdBy.roles');

        return $batch->createdBy?->hasRole('admin_desa') ?? false;
    }

    private function assertForwardAllowed(User $actor, SubmissionBatch $batch): void
    {
        if ((int) $actor->kecamatan_id !== (int) $batch->kecamatan_id) {
            throw new AuthorizationException('Admin Kecamatan hanya dapat meneruskan batch dari wilayahnya.');
        }
    }

    private function applyStatusChange(SubmissionBatch $batch, string $newStatus, User $actor, ?string $notes): SubmissionBatch
    {
        $isFinalReview = in_array($newStatus, [SubmissionBatch::STATUS_APPROVED, SubmissionBatch::STATUS_REJECTED], true);

        $batch->forceFill([
            'status' => $newStatus,
            'submitted_at' => $newStatus === SubmissionBatch::STATUS_PENDING ? now() : $batch->submitted_at,
            'verified_at' => $isFinalReview ? now() : $batch->verified_at,
            'verified_by' => $isFinalReview ? $actor->id : $batch->verified_by,
            'version' => (int) $batch->version + 1,
            'notes' => $notes ? trim(($batch->notes ? $batch->notes."\n" : '').$notes) : $batch->notes,
        ])->saveQuietly();

        return $batch->fresh(['kecamatan', 'createdBy', 'verifiedBy', 'items.entity']);
    }

    /**
     * Forward-to-kominfo: sets forwarded_at/forwarded_by atomically and bumps version.
     * Replaces the old bumpVersion() which had no forward-tracking semantics.
     */
    private function applyForward(SubmissionBatch $batch, User $actor, ?string $notes): SubmissionBatch
    {
        $batch->forceFill([
            'forwarded_at' => now(),
            'forwarded_by' => $actor->id,
            'version' => (int) $batch->version + 1,
            'notes' => $notes ? trim(($batch->notes ? $batch->notes."\n" : '').$notes) : $batch->notes,
        ])->saveQuietly();

        return $batch->fresh(['kecamatan', 'createdBy', 'verifiedBy', 'items.entity']);
    }

    private function recordWorkflowHistory(SubmissionBatch $batch, string $oldStatus, string $newStatus, User $actor, ?string $catatan, bool $isForward = false): void
    {
        $workflowInstanceId = $this->getOrCreateWorkflowInstance($batch, $actor);

        DB::table('workflow_instances')
            ->where('id', $workflowInstanceId)
            ->update([
                'status_id' => $this->workflowStatusId($newStatus),
                'kecamatan_id' => $batch->kecamatan_id,
                'dibuat_oleh' => $batch->created_by,
                'diverifikasi_oleh' => in_array($newStatus, [SubmissionBatch::STATUS_APPROVED, SubmissionBatch::STATUS_REJECTED], true) ? $actor->id : $batch->verified_by,
                'tanggal_submit' => $batch->submitted_at,
                'tanggal_verifikasi' => $batch->verified_at,
                'updated_at' => now(),
            ]);

        DB::table('workflow_histories')->insert([
            'workflow_instance_id' => $workflowInstanceId,
            'status_sebelumnya' => $this->workflowStatusId($oldStatus),
            'status_baru' => $this->workflowStatusId($newStatus),
            'catatan' => $catatan,
            'alasan' => in_array($newStatus, [SubmissionBatch::STATUS_REJECTED, SubmissionBatch::STATUS_REVISION], true) ? $catatan : null,
            'dilakukan_oleh' => $actor->id,
            'role' => $actor->roles->pluck('name')->first(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);

        if ($catatan) {
            DB::table('verification_comments')->insert([
                'workflow_instance_id' => $workflowInstanceId,
                'pengguna_id' => $actor->id,
                'komentar' => $catatan,
                'tipe' => $isForward ? 'forward_to_kominfo' : $newStatus,
                'created_at' => now(),
            ]);
        }
    }

    private function getOrCreateWorkflowInstance(SubmissionBatch $batch, User $actor): int
    {
        $existing = DB::table('workflow_instances')
            ->where('submission_batch_id', $batch->id)
            ->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('workflow_instances')->insertGetId([
            'entity_type' => SubmissionBatch::class,
            'entity_id' => $batch->id,
            'submission_batch_id' => $batch->id,
            'status_id' => $this->workflowStatusId($batch->status),
            'kecamatan_id' => $batch->kecamatan_id,
            'desa_id' => null,
            'dibuat_oleh' => $batch->created_by ?? $actor->id,
            'diverifikasi_oleh' => $batch->verified_by,
            'tanggal_submit' => $batch->submitted_at,
            'tanggal_verifikasi' => $batch->verified_at,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function getWorkflowStatusId(string $status): ?int
    {
        return $this->workflowStatusId($status);
    }

    private function workflowStatusId(string $status): ?int
    {
        return DB::table('workflow_status')->where('kode', $status)->value('id');
    }

    /**
     * SPRINT 6 FIX: Improved notification dispatch
     */
    private function dispatchNotification(SubmissionBatch $batch, User $actor, string $notificationType, string $oldStatus): void
    {
        match ($notificationType) {
            // Submit oleh Admin Desa → notify Admin Kecamatan
            'submit' => $actor->hasRole('admin_desa')
                ? $this->notifier->notifyKecamatanAboutDesaBatch($batch, $actor)
                : $this->notifier->notifyKominfoAwaitingVerification($batch, $actor),

            // Forward ke Kominfo → notify Kominfo
            'forward_to_kominfo' => $this->notifier->notifyKominfoAwaitingVerification($batch, $actor),

            // Approve → notify creator, notify kecamatan (jika kecamatan approve), notify kominfo
            'approve' => $this->dispatchApproveNotification($batch, $actor),

            // Reject → notify creator
            'reject' => $this->notifier->notifyCreator($batch, $batch->status, $actor),

            // Revision → notify creator
            'revision' => $this->notifier->notifyCreator($batch, $batch->status, $actor),

            // Archive → notify creator
            'archive' => $this->notifier->notifyCreator($batch, $batch->status, $actor),

            // Default → notify kecamatan dan creator
            default => $this->notifier->notifyKecamatanStatusChanged($batch, $batch->status, $actor),
        };
    }

    /**
     * SPRINT 6: Dispatch approve notification based on actor role.
     */
    private function dispatchApproveNotification(SubmissionBatch $batch, User $actor): void
    {
        // Notify creator
        $this->notifier->notifyCreator($batch, $batch->status, $actor);

        // Notify Admin Kecamatan tentang batch yang disetujui
        $this->notifier->notifyKecamatanStatusChanged($batch, $batch->status, $actor);

        // Notify Admin Kominfo tentang batch yang disetujui (agar mereka tahu ada batch baru yang disetujui kecamatan)
        $this->notifier->notifyKominfoBatchApproved($batch, $actor);
    }
}
