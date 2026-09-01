<?php

namespace App\Services\Notification;

use App\Models\SubmissionBatch;
use App\Models\User;
use App\Notifications\BatchNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * SPRINT 6: Notification Service terpusat.
 * SERVICE LAYER: Arsitektur §7.3
 * Semua notifikasi di sistem melewati service ini untuk konsistensi.
 */
class NotificationService
{
    /**
     * Kirim notifikasi ke Admin Kecamatan tentang batch baru dari Admin Desa.
     */
    public function notifyKecamatanBatchBaru(SubmissionBatch $batch, User $actor): void
    {
        $recipients = $this->getKecamatanAdmins($batch->kecamatan_id);

        if ($recipients->isEmpty()) {
            return;
        }

        $message = sprintf(
            'Admin Desa %s telah mengirim batch %s (%s) untuk %s. Silakan review sebelum diteruskan ke Kominfo.',
            $actor->name,
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module),
            $batch->kecamatan?->nama ?? '-'
        );

        $this->sendNotification($recipients, 'Batch Baru Menunggu Review', $message, $batch, $actor, 'warning');
    }

    /**
     * Kirim notifikasi ke Kominfo tentang batch yang menunggu verifikasi.
     */
    public function notifyKominfoBatchMenunggu(SubmissionBatch $batch, User $actor): void
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

        $this->sendNotification($recipients, 'Batch Menunggu Verifikasi', $message, $batch, $actor, 'info');
    }

    /**
     * Kirim notifikasi ke creator tentang status batch.
     */
    public function notifyCreatorBatchStatus(SubmissionBatch $batch, string $status, User $actor): void
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

        $this->sendNotification($creator, "Batch {$batch->submission_number} {$statusLabel}", $message, $batch, $actor, $this->statusColor($status));
    }

    /**
     * Kirim notifikasi ke Admin Kecamatan tentang perubahan status batch.
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

        $this->sendNotification($recipients, 'Status Batch Berubah', $message, $batch, $actor, $this->statusColor($status));
    }

    /**
     * Kirim notifikasi ke Admin OPD tentang batch baru.
     */
    public function notifyOpdBatchBaru(SubmissionBatch $batch, User $actor, array $roles): void
    {
        $recipients = $this->getUsersByRoles($roles);

        if ($recipients->isEmpty()) {
            return;
        }

        $message = sprintf(
            '%s telah mengirim batch %s (%s). Silakan review dan setujui.',
            $actor->name,
            $batch->submission_number,
            SubmissionBatch::moduleLabel($batch->module)
        );

        $this->sendNotification($recipients, 'Batch Baru Menunggu Persetujuan', $message, $batch, $actor, 'warning');
    }

    /**
     * Kirim notifikasi persetujuan batch.
     */
    public function notifyBatchApproved(SubmissionBatch $batch, User $actor): void
    {
        $this->notifyCreatorBatchStatus($batch, SubmissionBatch::STATUS_APPROVED, $actor);
        $this->notifyKecamatanStatusChanged($batch, SubmissionBatch::STATUS_APPROVED, $actor);
    }

    /**
     * Kirim notifikasi penolakan batch.
     */
    public function notifyBatchRejected(SubmissionBatch $batch, User $actor, ?string $revisionCatatan = null): void
    {
        $this->notifyCreatorBatchStatus($batch, SubmissionBatch::STATUS_REJECTED, $actor);

        if ($revisionCatatan) {
            $this->notifyCreatorBatchStatus($batch, SubmissionBatch::STATUS_REVISION, $actor);
        }
    }

    /**
     * Kirim notifikasi permintaan revisi.
     */
    public function notifyBatchRevision(SubmissionBatch $batch, User $actor, ?string $alasan = null): void
    {
        $this->notifyCreatorBatchStatus($batch, SubmissionBatch::STATUS_REVISION, $actor);

        if ($alasan) {
            $creator = $batch->createdBy;
            if ($creator) {
                $message = sprintf(
                    'Batch %s memerlukan revisi. Alasan: %s',
                    $batch->submission_number,
                    $alasan
                );
                $this->sendNotification($creator, "Batch {$batch->submission_number} Perlu Revisi", $message, $batch, $actor, 'warning');
            }
        }
    }

    /**
     * Kirim notifikasi ke Admin Desa tentang status submisi mobile.
     */
    public function notifyMobileSubmissionStatus(int $userId, SubmissionBatch $batch, string $status): void
    {
        $user = User::query()->find($userId);

        if (! $user) {
            return;
        }

        $statusLabel = $this->statusLabel($status);
        $message = sprintf(
            'Status submisi mobile Anda (%s) saat ini: %s',
            $batch->submission_number,
            $statusLabel
        );

        $this->sendNotification($user, "Submisi Mobile {$batch->submission_number}", $message, $batch, null, $this->statusColor($status));
    }

    // ========================
    // Helper Methods
    // ========================

    private function sendNotification(
        User|Collection $recipients,
        string $title,
        string $message,
        SubmissionBatch $batch,
        ?User $actor,
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

    private function getKecamatanAdmins(?int $kecamatanId): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin_kecamatan'))
            ->when($kecamatanId, fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->get();
    }

    private function getKominfoAdmins(): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', [
                'admin',
                'admin_kominfo',
                'super_admin',
            ]))
            ->get();
    }

    private function getUsersByRoles(array $roles): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
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
