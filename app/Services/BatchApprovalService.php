<?php

namespace App\Services;

use App\Models\SubmissionBatch;
use App\Models\User;
use App\Notifications\BatchNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class BatchApprovalService
{
    public function submit(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null, bool $notify = true): SubmissionBatch
    {
        return $this->transition($batch, SubmissionBatch::STATUS_PENDING, $actor, $catatan, $notify, 'submit');
    }

    public function startReview(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null): SubmissionBatch
    {
        return $this->transition($batch, SubmissionBatch::STATUS_IN_REVIEW, $actor, $catatan, false, null);
    }

    public function approve(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null, bool $notify = true): SubmissionBatch
    {
        return $this->transition($batch, SubmissionBatch::STATUS_APPROVED, $actor, $catatan, $notify, 'kecamatan');
    }

    public function reject(SubmissionBatch $batch, ?User $actor = null, ?string $alasan = null, bool $notify = true): SubmissionBatch
    {
        return $this->transition($batch, SubmissionBatch::STATUS_REJECTED, $actor, $alasan, $notify, 'kecamatan');
    }

    public function requestRevision(SubmissionBatch $batch, ?User $actor = null, ?string $alasan = null, bool $notify = true): SubmissionBatch
    {
        return $this->transition($batch, SubmissionBatch::STATUS_REVISION, $actor, $alasan, $notify, 'kecamatan');
    }

    public function archive(SubmissionBatch $batch, ?User $actor = null, ?string $catatan = null, bool $notify = true): SubmissionBatch
    {
        return $this->transition($batch, SubmissionBatch::STATUS_ARCHIVED, $actor, $catatan, $notify, 'kecamatan');
    }

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
                'catatan' => 'Batch dikirim ke Kominfo',
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

    private function transition(SubmissionBatch $batch, string $statusBaru, ?User $actor, ?string $catatan, bool $notify, ?string $notificationType): SubmissionBatch
    {
        $actor ??= Auth::user();

        if (! $actor) {
            throw new AuthorizationException('Pengguna belum terautentikasi.');
        }

        return DB::transaction(function () use ($batch, $statusBaru, $actor, $catatan, $notify, $notificationType): SubmissionBatch {
            $lockedBatch = SubmissionBatch::query()
                ->whereKey($batch->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $statusLama = $lockedBatch->status;
            $updatedBatch = $this->changeBatchStatus($lockedBatch, $statusBaru, $actor, $catatan);
            $this->recordWorkflowTransition($updatedBatch, $statusLama, $statusBaru, $actor, $catatan);

            if ($notify && $notificationType !== null) {
                DB::afterCommit(function () use ($updatedBatch, $actor, $notificationType): void {
                    if ($notificationType === 'submit') {
                        $this->notifyKominfo($updatedBatch, $actor);

                        return;
                    }

                    $this->notifyKecamatan($updatedBatch, $updatedBatch->status, $actor);
                });
            }

            return $updatedBatch;
        });
    }

    private function changeBatchStatus(SubmissionBatch $batch, string $statusBaru, User $actor, ?string $catatan = null): SubmissionBatch
    {
        $this->assertTransitionAllowed($actor, $batch, $statusBaru);

        $verifiedAt = match ($statusBaru) {
            SubmissionBatch::STATUS_APPROVED, SubmissionBatch::STATUS_REJECTED => now(),
            SubmissionBatch::STATUS_ARCHIVED => $batch->verified_at,
            default => null,
        };

        $verifiedBy = match ($statusBaru) {
            SubmissionBatch::STATUS_APPROVED, SubmissionBatch::STATUS_REJECTED => $actor->id,
            SubmissionBatch::STATUS_ARCHIVED => $batch->verified_by,
            default => null,
        };

        $batch->forceFill([
            'status' => $statusBaru,
            'submitted_at' => $statusBaru === SubmissionBatch::STATUS_PENDING ? now() : $batch->submitted_at,
            'verified_at' => $verifiedAt,
            'verified_by' => $verifiedBy,
            'version' => (int) $batch->version + 1,
            'notes' => $catatan ? trim(($batch->notes ? $batch->notes."\n" : '').$catatan) : $batch->notes,
        ])->saveQuietly();

        return $batch->fresh(['kecamatan', 'createdBy', 'verifiedBy', 'items.entity']);
    }

    private function recordWorkflowTransition(SubmissionBatch $batch, string $statusLama, string $statusBaru, User $actor, ?string $catatan = null): void
    {
        $workflowInstanceId = $this->workflowInstanceId($batch, $actor);
        $previousStatusId = $this->workflowStatusId($statusLama);
        $newStatusId = $this->workflowStatusId($statusBaru);

        DB::table('workflow_instances')
            ->where('id', $workflowInstanceId)
            ->update([
                'status_id' => $newStatusId,
                'kecamatan_id' => $batch->kecamatan_id,
                'dibuat_oleh' => $batch->created_by,
                'diverifikasi_oleh' => in_array($statusBaru, [SubmissionBatch::STATUS_APPROVED, SubmissionBatch::STATUS_REJECTED], true) ? $actor->id : $batch->verified_by,
                'tanggal_submit' => $batch->submitted_at,
                'tanggal_verifikasi' => $batch->verified_at,
                'updated_at' => now(),
            ]);

        DB::table('workflow_histories')->insert([
            'workflow_instance_id' => $workflowInstanceId,
            'status_sebelumnya' => $previousStatusId,
            'status_baru' => $newStatusId,
            'catatan' => $catatan,
            'alasan' => in_array($statusBaru, [SubmissionBatch::STATUS_REJECTED, SubmissionBatch::STATUS_REVISION], true) ? $catatan : null,
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
                'tipe' => $statusBaru,
                'created_at' => now(),
            ]);
        }
    }

    private function workflowInstanceId(SubmissionBatch $batch, User $actor): int
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

    private function workflowStatusId(string $status): ?int
    {
        return DB::table('workflow_status')->where('kode', $status)->value('id');
    }

    private function assertTransitionAllowed(User $actor, SubmissionBatch $batch, string $statusBaru): void
    {
        $allowedTransitions = [
            SubmissionBatch::STATUS_DRAFT => [SubmissionBatch::STATUS_PENDING],
            SubmissionBatch::STATUS_PENDING => [SubmissionBatch::STATUS_IN_REVIEW],
            SubmissionBatch::STATUS_IN_REVIEW => [
                SubmissionBatch::STATUS_APPROVED,
                SubmissionBatch::STATUS_REJECTED,
                SubmissionBatch::STATUS_REVISION,
            ],
            SubmissionBatch::STATUS_REVISION => [SubmissionBatch::STATUS_PENDING],
            SubmissionBatch::STATUS_APPROVED => [SubmissionBatch::STATUS_ARCHIVED],
            SubmissionBatch::STATUS_REJECTED => [SubmissionBatch::STATUS_ARCHIVED],
            SubmissionBatch::STATUS_ARCHIVED => [],
        ];

        if (! in_array($statusBaru, $allowedTransitions[$batch->status] ?? [], true)) {
            throw new AuthorizationException(sprintf(
                'Transisi batch dari [%s] ke [%s] tidak diizinkan.',
                $batch->status,
                $statusBaru,
            ));
        }

        if ($statusBaru === SubmissionBatch::STATUS_PENDING && $actor->hasRole('admin_kecamatan')) {
            if ((int) $actor->kecamatan_id !== (int) $batch->kecamatan_id) {
                throw new AuthorizationException('Admin Kecamatan tidak dapat mengirim batch kecamatan lain.');
            }

            return;
        }

        if ($statusBaru !== SubmissionBatch::STATUS_PENDING && $actor->hasRole('admin')) {
            return;
        }

        throw new AuthorizationException('Transisi batch tidak diizinkan untuk role ini.');
    }

    private function notifyKominfo(SubmissionBatch $batch, User $actor): void
    {
        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'admin'))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $title = SubmissionBatch::moduleLabel($batch->module).' menunggu verifikasi';
        $message = sprintf(
            'Batch %s untuk %s #%s menunggu verifikasi.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-',
        );

        NotificationFacade::send(
            $recipients,
            new BatchNotification(
                $title,
                $message,
                class_basename($batch),
                $batch->getKey(),
                $actor->id,
                $actor->name,
            ),
        );
    }

    private function notifyKecamatan(SubmissionBatch $batch, string $status, User $actor): void
    {
        if (! $batch->kecamatan_id) {
            return;
        }

        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'admin_kecamatan'))
            ->where('kecamatan_id', $batch->kecamatan_id)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $statusLabel = match ($status) {
            SubmissionBatch::STATUS_APPROVED => 'disetujui',
            SubmissionBatch::STATUS_REJECTED => 'ditolak',
            SubmissionBatch::STATUS_REVISION => 'diminta revisi',
            SubmissionBatch::STATUS_ARCHIVED => 'diarsipkan',
            default => 'diperbarui',
        };

        NotificationFacade::send(
            $recipients,
            new BatchNotification(
                'Status batch telah '.$statusLabel,
                sprintf(
                    'Batch %s untuk %s #%s telah %s.',
                    $batch->submission_number,
                    SubmissionBatch::moduleLabel($batch->module),
                    $batch->kecamatan?->nama ?? '-',
                    $statusLabel,
                ),
                class_basename($batch),
                $batch->getKey(),
                $actor->id,
                $actor->name,
            ),
        );
    }

}
