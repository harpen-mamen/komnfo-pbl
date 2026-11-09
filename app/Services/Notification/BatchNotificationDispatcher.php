<?php

namespace App\Services\Notification;

use App\Models\SubmissionBatch;
use App\Models\User;
use App\Notifications\BatchNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Sprint 6: Enhanced BatchNotificationDispatcher
 *
 * Notification types:
 * - notifyKecamatanAboutDesaBatch: Admin Desa submit → Admin Kecamatan
 * - notifyKominfoAwaitingVerification: Batch menunggu verifikasi Kominfo
 * - notifyKominfoBatchApproved: Batch yang sudah disetujui kecamatan
 * - notifyCreator: Status perubahan ke creator batch
 * - notifyKecamatanStatusChanged: Status perubahan ke Admin Kecamatan
 */
class BatchNotificationDispatcher
{
    /**
     * Notify Admin Kecamatan tentang batch baru dari Admin Desa.
     */
    public function notifyKecamatanAboutDesaBatch(SubmissionBatch $batch, User $actor): void
    {
        $recipients = $this->getKecamatanAdmins($batch->kecamatan_id);

        if ($recipients->isEmpty()) {
            return;
        }

        $message = sprintf(
            '%s telah mengirim batch %s (%s) untuk %s. Silakan periksa sebelum diteruskan ke Walidata.',
            $actor->name,
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-'
        );

        $this->sendNotification(
            $recipients,
            'Batch Baru dari Admin Lapangan',
            $message,
            $batch,
            $actor,
            'warning'
        );
    }

    /**
     * Notify Admin Kominfo bahwa ada batch yang menunggu verifikasi.
     */
    public function notifyKominfoAwaitingVerification(SubmissionBatch $batch, User $actor): void
    {
        $recipients = $this->getKominfoAdmins();

        if ($recipients->isEmpty()) {
            return;
        }

        $message = sprintf(
            'Batch %s (%s) untuk %s menunggu verifikasi.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-'
        );

        $this->sendNotification(
            $recipients,
            'Batch Menunggu Verifikasi Kominfo',
            $message,
            $batch,
            $actor,
            'info'
        );
    }

    /**
     * SPRINT 6: Notify Admin Kominfo bahwa ada batch yang sudah disetujui Admin Kecamatan.
     */
    public function notifyKominfoBatchApproved(SubmissionBatch $batch, User $actor): void
    {
        $recipients = $this->getKominfoAdmins();

        if ($recipients->isEmpty()) {
            return;
        }

        $roleName = $actor->roles->pluck('name')->first() ?? 'Unknown';
        $message = sprintf(
            'Batch %s (%s) untuk %s telah disetujui oleh %s (%s). Batch telah dipublikasikan.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-',
            $actor->name,
            $roleName
        );

        $this->sendNotification(
            $recipients,
            'Batch Disetujui Kecamatan',
            $message,
            $batch,
            $actor,
            'success'
        );
    }

    /**
     * Notify Admin Kecamatan tentang perubahan status batch.
     */
    public function notifyKecamatanStatusChanged(SubmissionBatch $batch, string $status, User $actor): void
    {
        if (! $batch->kecamatan_id) {
            return;
        }

        $recipients = $this->getKecamatanAdmins($batch->kecamatan_id);

        if ($recipients->isEmpty()) {
            return;
        }

        $statusLabel = $this->statusLabel($status);
        $message = sprintf(
            'Batch %s (%s) untuk %s telah %s oleh %s.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-',
            $statusLabel,
            $actor->name
        );

        $this->sendNotification(
            $recipients,
            'Status Batch Berubah',
            $message,
            $batch,
            $actor,
            $this->statusColor($status)
        );
    }

    /**
     * Notify creator batch tentang perubahan status.
     */
    public function notifyCreator(SubmissionBatch $batch, string $status, User $actor): void
    {
        $creator = $batch->createdBy;

        if (! $creator || (int) $creator->id === (int) $actor->id) {
            return;
        }

        $statusLabel = $this->statusLabel($status);
        $message = sprintf(
            'Batch %s (%s) untuk %s telah %s oleh %s.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-',
            $statusLabel,
            $actor->name
        );

        $this->sendNotification(
            $creator,
            "Batch {$batch->submission_number} {$statusLabel}",
            $message,
            $batch,
            $actor,
            $this->statusColor($status)
        );
    }

    /**
     * SPRINT 6: Notify Admin Desa bahwa batch-nya sudah disetujui.
     */
    public function notifyDesaBatchApproved(SubmissionBatch $batch, User $actor): void
    {
        $creator = $batch->createdBy;

        if (! $creator) {
            return;
        }

        $message = sprintf(
            'Batch %s (%s) untuk %s telah disetujui dan dipublikasikan.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-'
        );

        $this->sendNotification(
            $creator,
            "Batch {$batch->submission_number} Disetujui",
            $message,
            $batch,
            $actor,
            'success'
        );
    }

    /**
     * SPRINT 6: Notify Admin Desa bahwa batch-nya diminta revisi.
     */
    public function notifyDesaBatchRevision(SubmissionBatch $batch, User $actor, string $alasan): void
    {
        $creator = $batch->createdBy;

        if (! $creator) {
            return;
        }

        $message = sprintf(
            'Batch %s (%s) memerlukan revisi. Alasan: %s',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $alasan
        );

        $this->sendNotification(
            $creator,
            "Batch {$batch->submission_number} Perlu Revisi",
            $message,
            $batch,
            $actor,
            'warning'
        );
    }

    /**
     * SPRINT 6: Notify Admin Desa bahwa batch-nya ditolak.
     */
    public function notifyDesaBatchRejected(SubmissionBatch $batch, User $actor, string $alasan): void
    {
        $creator = $batch->createdBy;

        if (! $creator) {
            return;
        }

        $message = sprintf(
            'Batch %s (%s) telah ditolak. Alasan: %s',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $alasan
        );

        $this->sendNotification(
            $creator,
            "Batch {$batch->submission_number} Ditolak",
            $message,
            $batch,
            $actor,
            'danger'
        );
    }

    public function notifyCrossVerificators(SubmissionBatch $batch, User $actor, array $verifikatorRoles): void
    {
        if (empty($verifikatorRoles)) {
            return;
        }

        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', $verifikatorRoles))
            ->where('kecamatan_id', $batch->kecamatan_id)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $recipient) {
            NotificationFacade::sendNow(
                $recipient,
                $this->makeNotification(
                    'Verifikasi silang diperlukan',
                    sprintf(
                        'Batch %s untuk %s menunggu verifikasi silang.',
                        $batch->submission_number,
                        SubmissionBatch::moduleLabel($batch->module)
                    ),
                    $batch,
                    $actor,
                    'info'
                )
            );
        }
    }

    public function notifyPuskesStatusChanged(SubmissionBatch $batch, string $status, User $actor): void
    {
        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['admin_puskesmas', 'operator_puskes']))
            ->when($batch->kecamatan_id, fn ($q) => $q->where('kecamatan_id', $batch->kecamatan_id))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $statusLabel = $this->statusLabel($status);
        $message = sprintf(
            'Batch %s (%s) untuk Kecamatan %s telah %s oleh %s.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-',
            $statusLabel,
            $actor->name
        );

        $this->sendNotification(
            $recipients,
            "Batch {$batch->submission_number} {$statusLabel}",
            $message,
            $batch,
            $actor,
            $this->statusColor($status)
        );
    }

    public function notifySekolahStatusChanged(SubmissionBatch $batch, string $status, User $actor): void
    {
        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['admin_sekolah', 'operator_sekolah']))
            ->when($batch->kecamatan_id, fn ($q) => $q->where('kecamatan_id', $batch->kecamatan_id))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $statusLabel = $this->statusLabel($status);
        $message = sprintf(
            'Batch %s (%s) untuk Kecamatan %s telah %s oleh %s.',
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-',
            $statusLabel,
            $actor->name
        );

        $this->sendNotification(
            $recipients,
            "Batch {$batch->submission_number} {$statusLabel}",
            $message,
            $batch,
            $actor,
            $this->statusColor($status)
        );
    }

    public function notifyOpdAboutPuskesSekolahBatch(SubmissionBatch $batch, User $actor): void
    {
        $roles = match ($batch->module) {
            SubmissionBatch::MODULE_PUSKES => ['admin_kesehatan', 'admin_dinkes', 'admin_puskesmas'],
            SubmissionBatch::MODULE_SEKOLAH => ['admin_pendidikan', 'admin_dispend', 'admin_sekolah'],
            default => [],
        };

        if (empty($roles)) {
            return;
        }

        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', $roles))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $message = sprintf(
            '%s telah mengirim batch %s (%s). Silakan review dan setujui.',
            $actor->name,
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module)
        );

        $this->sendNotification(
            $recipients,
            'Batch Baru Menunggu Persetujuan',
            $message,
            $batch,
            $actor,
            'warning'
        );
    }

    // ========================
    // Helper Methods
    // ========================

    private function sendNotification(
        User|Collection $recipients,
        string $title,
        string $message,
        SubmissionBatch $batch,
        User $actor,
        string $color = 'info'
    ): void {
        $notification = new BatchNotification(
            $title,
            $message,
            class_basename($batch),
            $batch->getKey(),
            $actor?->id ?? 0,
            $actor?->name ?? 'System'
        );

        if ($recipients instanceof User) {
            $recipients->notify($notification);
        } else {
            NotificationFacade::sendNow($recipients, $notification);
        }
    }

    private function makeNotification(
        string $title,
        string $message,
        SubmissionBatch $batch,
        User $actor,
        string $color = 'info'
    ): BatchNotification {
        return new BatchNotification(
            $title,
            $message,
            class_basename($batch),
            $batch->getKey(),
            $actor?->id ?? 0,
            $actor?->name ?? 'System'
        );
    }

    private function getKecamatanAdmins(?int $kecamatanId): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->where('name', 'admin_kecamatan'))
            ->when($kecamatanId, fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->get();
    }

    private function getKominfoAdmins(): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                'admin',
            'admin_kominfo',
            'walidata',
                'super_admin',
            ]))
            ->get();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            SubmissionBatch::STATUS_DRAFT => 'draft',
            SubmissionBatch::STATUS_PENDING => 'menunggu verifikasi',
            SubmissionBatch::STATUS_IN_REVIEW => 'sedang diverifikasi',
            SubmissionBatch::STATUS_REVISION => 'diminta revisi',
            SubmissionBatch::STATUS_APPROVED => 'disetujui',
            SubmissionBatch::STATUS_REJECTED => 'ditolak',
            SubmissionBatch::STATUS_ARCHIVED => 'diarsipkan',
            default => 'diperbarui',
        };
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            SubmissionBatch::STATUS_APPROVED => 'success',
            SubmissionBatch::STATUS_REJECTED => 'danger',
            SubmissionBatch::STATUS_REVISION => 'warning',
            SubmissionBatch::STATUS_PENDING => 'info',
            default => 'gray',
        };
    }
}
