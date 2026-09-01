<?php

namespace App\Filament\Resources\SubmissionBatchResource\Pages;

use App\Filament\Resources\SubmissionBatchResource;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use App\Services\BatchApprovalService;
use App\Support\PanelAccess;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewSubmissionBatch extends ViewRecord
{
    protected static string $resource = SubmissionBatchResource::class;
    protected static string $view = 'filament.pages.submission-batch-review';

    protected function getTitle(): string
    {
        return PanelAccess::isAdminKecamatan()
            ? 'Review Verifikasi Tingkat Kecamatan'
            : 'Review Verifikasi Kominfo';
    }

    public function getBreadcrumb(): string
    {
        return 'Review';
    }

    /**
     * SPRINT 6: Simplified actions based on user role and batch status.
     *
     * SIPANDU Workflow:
     * Flow for Admin Desa:
     *   - Create batch → PENDING → notification to Admin Kecamatan
     *
     * Flow for Admin Kecamatan:
     *   - See batch from Admin Desa → PENDING
     *   - "Mulai Review" → IN_REVIEW
     *   - "Tolak" → REJECTED
     *   - "Minta Revisi" → REVISION (Admin Desa can resubmit)
     *   - "Teruskan ke Kominfo" → IN_REVIEW (forwarded_at set) → notify Kominfo
     *   ⚠ Admin Kecamatan CANNOT approve master data (rumah/keluarga/penduduk)
     *
     * Flow for Admin Kominfo:
     *   - See batch forwarded from Admin Kecamatan → IN_REVIEW
     *   - "Setujui" → APPROVED + publish
     *   - "Tolak" → REJECTED
     *   - "Minta Revisi" → REVISION
     */
    protected function getActions(): array
    {
        $actions = [];

        // 1. Mulai Review (Kecamatan/Kominfo) - only for PENDING batches
        $actions[] = Actions\Action::make('startReview')
            ->label('Mulai Review')
            ->color('primary')
            ->icon('heroicon-o-clipboard-check')
            ->visible(fn (): bool =>
                $this->record->status === SubmissionBatch::STATUS_PENDING
                && (
                    (PanelAccess::isAdminKecamatan() && $this->isDesaSubmission())
                    || PanelAccess::isKominfoKabupaten()
                )
            )
            ->action(function (): void {
                $this->record = app(BatchApprovalService::class)->startReview(
                    $this->record,
                    PanelAccess::user()
                );

                Notification::make()
                    ->title('Review dimulai')
                    ->body('Batch sedang dalam proses review.')
                    ->success()
                    ->send();
            });

        // 2. Setujui (Kecamatan untuk batch wilayahnya, atau Kominfo)
        $actions[] = Actions\Action::make('approve')
            ->label('Setujui')
            ->color('success')
            ->icon('heroicon-o-check')
            ->form([
                Textarea::make('catatan')
                    ->label('Catatan Persetujuan')
                    ->placeholder('Catatan opsional...')
                    ->rows(2),
            ])
            ->visible(fn (): bool =>
                $this->record->status === SubmissionBatch::STATUS_IN_REVIEW
                && $this->canApproveCurrentBatch()
            )
            ->action(function (array $data): void {
                try {
                    $this->record = app(BatchApprovalService::class)->approve(
                        $this->record,
                        PanelAccess::user(),
                        $data['catatan'] ?? null
                    );

                    Notification::make()
                        ->title('Batch Disetujui')
                        ->body('Batch telah disetujui dan dipublikasikan.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });

        // 3. Tolak (Admin Kecamatan CAN reject; Kominfo can reject all)
        $actions[] = Actions\Action::make('reject')
            ->label('Tolak')
            ->color('danger')
            ->icon('heroicon-o-x')
            ->form([
                Textarea::make('catatan')
                    ->label('Alasan Penolakan')
                    ->placeholder('Jelaskan alasan penolakan...')
                    ->required(),
            ])
            ->requiresConfirmation()
            ->visible(fn (): bool =>
                $this->record->status === SubmissionBatch::STATUS_IN_REVIEW
                && $this->canRejectOrRequestRevision()
            )
            ->action(function (array $data): void {
                $this->record = app(BatchApprovalService::class)->reject(
                    $this->record,
                    PanelAccess::user(),
                    $data['catatan']
                );

                Notification::make()
                    ->title('Batch Ditolak')
                    ->body('Batch telah ditolak. Admin Desa akan diberitahu.')
                    ->danger()
                    ->send();
            });

        // 4. Minta Revisi (Admin Kecamatan CAN request revision; Kominfo can request revision)
        $actions[] = Actions\Action::make('requestRevision')
            ->label('Minta Revisi')
            ->color('warning')
            ->icon('heroicon-o-arrow-right')
            ->form([
                Textarea::make('catatan')
                    ->label('Catatan / Alasan Revisi')
                    ->placeholder('Jelaskan apa yang perlu direvisi...')
                    ->required(),
            ])
            ->requiresConfirmation()
            ->visible(fn (): bool =>
                $this->record->status === SubmissionBatch::STATUS_IN_REVIEW
                && $this->canRejectOrRequestRevision()
            )
            ->action(function (array $data): void {
                $prefix = PanelAccess::isAdminKecamatan() ? '[Kecamatan] ' : '[Kominfo] ';
                $this->record = app(BatchApprovalService::class)->requestRevision(
                    $this->record,
                    PanelAccess::user(),
                    $prefix . $data['catatan']
                );

                Notification::make()
                    ->title('Permintaan Revisi Terkirim')
                    ->body('Admin Desa akan diminta untuk merevisi data.')
                    ->warning()
                    ->send();
            });

        // 5. Teruskan ke Kominfo (Kecamatan only — must NOT already be forwarded)
        $actions[] = Actions\Action::make('forwardToKominfo')
            ->label('Teruskan ke Kominfo')
            ->color('info')
            ->icon('heroicon-o-paper-airplane')
            ->form([
                Textarea::make('catatan')
                    ->label('Catatan')
                    ->placeholder('Catatan opsional untuk Kominfo...')
                    ->rows(2),
            ])
            ->requiresConfirmation()
            ->visible(fn (): bool =>
                PanelAccess::isAdminKecamatan()
                && $this->isDesaSubmission()
                && $this->record->status === SubmissionBatch::STATUS_IN_REVIEW
                && ! $this->record->isForwarded()
            )
            ->action(function (array $data): void {
                $this->record = app(BatchApprovalService::class)->forwardToKominfo(
                    $this->record,
                    PanelAccess::user(),
                    $data['catatan'] ?? null
                );

                Notification::make()
                    ->title('Batch Diteruskan ke Kominfo')
                    ->body('Admin Kominfo telah diberitahu untuk ikut mereview batch ini.')
                    ->info()
                    ->send();
            });

        // 6. Kirim Ulang (untuk batch REVISION)
        $actions[] = Actions\Action::make('resubmit')
            ->label('Kirim Ulang')
            ->color('warning')
            ->icon('heroicon-o-arrow-up')
            ->visible(fn (): bool =>
                $this->record->status === SubmissionBatch::STATUS_REVISION
                && PanelAccess::isAdminDesa()
            )
            ->action(function (): void {
                $this->record = app(BatchApprovalService::class)->submit(
                    $this->record,
                    PanelAccess::user()
                );

                Notification::make()
                    ->title('Batch Dikirim Ulang')
                    ->body('Batch telah dikirim ulang untuk diverifikasi.')
                    ->success()
                    ->send();
            });

        // 7. Arsipkan (Kominfo only - untuk batch yang sudah final)
        $actions[] = Actions\Action::make('archive')
            ->label('Arsipkan')
            ->color('gray')
            ->icon('heroicon-o-archive')
            ->requiresConfirmation()
            ->visible(fn (): bool =>
                PanelAccess::isKominfoKabupaten()
                && in_array($this->record->status, [
                    SubmissionBatch::STATUS_APPROVED,
                    SubmissionBatch::STATUS_REJECTED,
                ], true)
            )
            ->action(function (): void {
                $this->record = app(BatchApprovalService::class)->archive(
                    $this->record,
                    PanelAccess::user()
                );

                Notification::make()
                    ->title('Batch Diarsipkan')
                    ->body('Batch telah dipindahkan ke arsip.')
                    ->success()
                    ->send();
            });

        return $actions;
    }

    /**
     * Check if user can approve/reject the current batch.
     *
     * CRITICAL FIX C2: Admin Kecamatan CANNOT approve master-data batches.
     *   Only Admin Kominfo (or kabupaten admins) can approve master data.
     *   Admin Kecamatan can only: start review, reject, request revision, forward.
     */
    private function canApproveCurrentBatch(): bool
    {
        $user = PanelAccess::user();

        if (! $user) {
            return false;
        }

        // Admin Kominfo can approve all batches (after forward for master data)
        if (PanelAccess::isKominfoKabupaten()) {
            // For master-data batches, require that they were forwarded by Kecamatan
            if ($this->record->isMasterData() && ! $this->record->isForwarded()) {
                return false;
            }

            return true;
        }

        // Admin Kecamatan CANNOT approve master-data batches — they can only forward.
        if (PanelAccess::isAdminKecamatan()) {
            return false;
        }

        return false;
    }

    /**
     * Admin Kecamatan can reject/request-revision for batches in their wilayah.
     * Admin Kominfo can reject/request-revision for all batches.
     */
    private function canRejectOrRequestRevision(): bool
    {
        $user = PanelAccess::user();

        if (! $user) {
            return false;
        }

        // Admin Kominfo can reject/revision any batch in IN_REVIEW
        if (PanelAccess::isKominfoKabupaten()) {
            return true;
        }

        // Admin Kecamatan can reject/revision batches from their wilayah
        if (PanelAccess::isAdminKecamatan()) {
            $userKecamatanId = $user->kecamatan_id ?? $user->desa?->kecamatan_id;
            return (int) $userKecamatanId === (int) $this->record->kecamatan_id;
        }

        return false;
    }

    private function isDesaSubmission(): bool
    {
        $this->record->loadMissing('createdBy');

        return $this->record->createdBy?->hasRole('admin_desa') ?? false;
    }

    public function getEntitySummary(): array
    {
        $moduleLabel = SubmissionBatch::moduleLabel($this->record->module);

        return [
            ['label' => 'Batch', 'value' => $this->record->submission_number],
            ['label' => 'Modul', 'value' => $moduleLabel],
            ['label' => 'Kecamatan', 'value' => $this->record->kecamatan?->nama],
            ['label' => 'Bulan', 'value' => $this->record->periode_bulan ? $this->record->periode_bulan : '-'],
            ['label' => 'Semester', 'value' => $this->record->semester ? ucfirst($this->record->semester) : '-'],
            ['label' => 'Tahun', 'value' => $this->record->periode_tahun ?? '-'],
            ['label' => 'Status', 'value' => SubmissionBatch::statusLabel($this->record->status)],
            ['label' => 'Pengirim', 'value' => $this->record->createdBy?->name],
            ['label' => 'Role Pengirim', 'value' => $this->isDesaSubmission() ? 'Admin Desa' : 'Admin Kecamatan'],
            ['label' => 'Tanggal Kirim', 'value' => $this->record->submitted_at?->format('d M Y H:i')],
            ['label' => 'Diverifikasi Oleh', 'value' => $this->record->verifiedBy?->name],
            ['label' => 'Tanggal Verifikasi', 'value' => $this->record->verified_at?->format('d M Y H:i')],
        ];
    }

    public function getBatchMetrics(): array
    {
        return [
            ['label' => 'Total Item', 'value' => $this->record->items()->count()],
            ['label' => 'Total Rumah', 'value' => $this->record->items()->where('entity_type', Rumah::class)->count()],
            ['label' => 'Total KK', 'value' => $this->record->items()->where('entity_type', Keluarga::class)->count()],
            ['label' => 'Total Penduduk', 'value' => $this->record->items()->where('entity_type', Penduduk::class)->count()],
            ['label' => 'Total Pendidikan', 'value' => $this->record->items()->where('entity_type', DataPendidikanBulanan::class)->count()],
            ['label' => 'Total Kesehatan', 'value' => $this->record->items()->where('entity_type', DataKesehatanBulanan::class)->count()],
            ['label' => 'Catatan', 'value' => $this->record->notes],
        ];
    }

    public function getBatchItems(): array
    {
        $this->record->loadMissing(['items.entity', 'kecamatan']);

        return $this->record->items
            ->map(fn (SubmissionBatchItem $item): array => $this->summarizeItem($item))
            ->values()
            ->all();
    }

    public function getBatchItemsGroupedByModule(): array
    {
        $this->record->loadMissing(['items.entity']);

        return $this->record->items
            ->map(fn (SubmissionBatchItem $item): array => $this->summarizeItem($item))
            ->groupBy('module')
            ->map(fn ($items, string $module): array => [
                'module' => $module,
                'label' => SubmissionBatch::moduleLabel($module),
                'count' => $items->count(),
                'items' => $items->values()->all(),
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    public function getSnapshotModules(): array
    {
        $snapshot = $this->record->payload_snapshot ?? [];
        $modules = $snapshot['modules'] ?? null;

        if (is_array($modules)) {
            return collect($modules)
                ->map(fn (array $module): array => [
                    'module' => $module['module'] ?? '-',
                    'label' => $module['module_label'] ?? SubmissionBatch::moduleLabel($module['module'] ?? null),
                    'count' => $module['count'] ?? 0,
                    'periode_bulan' => $module['periode_bulan'] ?? null,
                    'semester' => $module['semester'] ?? null,
                    'periode_tahun' => $module['periode_tahun'] ?? null,
                    'entity_ids' => $module['entity_ids'] ?? [],
                ])
                ->values()
                ->all();
        }

        return [[
            'module' => $snapshot['module'] ?? $this->record->module,
            'label' => $snapshot['module_label'] ?? SubmissionBatch::moduleLabel($this->record->module),
            'count' => $snapshot['count'] ?? $this->record->items()->count(),
            'periode_bulan' => $snapshot['periode_bulan'] ?? $this->record->periode_bulan,
            'semester' => $snapshot['semester'] ?? $this->record->semester,
            'periode_tahun' => $snapshot['periode_tahun'] ?? $this->record->periode_tahun,
            'entity_ids' => $snapshot['entity_ids'] ?? [],
        ]];
    }

    public function getVerificationComments(): array
    {
        $workflowInstanceId = $this->workflowInstanceId();

        if (! $workflowInstanceId) {
            return [];
        }

        return DB::table('verification_comments')
            ->leftJoin('users', 'users.id', '=', 'verification_comments.pengguna_id')
            ->where('verification_comments.workflow_instance_id', $workflowInstanceId)
            ->orderBy('verification_comments.created_at')
            ->get([
                'verification_comments.komentar',
                'verification_comments.tipe',
                'verification_comments.created_at',
                'users.name as pengguna',
            ])
            ->map(fn ($comment): array => [
                'komentar' => $comment->komentar,
                'tipe' => SubmissionBatch::statusLabel($comment->tipe),
                'created_at' => \Illuminate\Support\Carbon::parse($comment->created_at),
                'pengguna' => $comment->pengguna,
            ])
            ->all();
    }

    public function getVersionHistory(): array
    {
        $this->record->loadMissing('items');
        $modules = $this->record->package_keys ?: [$this->record->module];
        $signature = implode('+', collect($modules)->sort()->values()->all());

        return SubmissionBatch::query()
            ->where('kecamatan_id', $this->record->kecamatan_id)
            ->where('package_signature', $signature)
            ->with(['createdBy', 'verifiedBy', 'items'])
            ->orderBy('id')
            ->get()
            ->filter(fn (SubmissionBatch $batch): bool => collect($modules)->every(
                fn (string $module): bool => $this->versionContainsSameModulePeriod($batch, $module)
            ))
            ->map(fn (SubmissionBatch $batch): array => [
                'id' => $batch->id,
                'submission_number' => $batch->submission_number,
                'snapshot_version' => $batch->payload_snapshot['snapshot_version'] ?? $batch->payload_snapshot['revision']['number'] ?? $batch->version,
                'status' => SubmissionBatch::statusLabel($batch->status),
                'is_current' => $batch->id === $this->record->id,
                'is_active' => $batch->id === $this->latestActiveVersionId($modules),
                'submitted_at' => $batch->submitted_at,
                'reviewed_at' => $batch->verified_at,
                'reviewer' => $batch->verifiedBy?->name,
                'sender' => $batch->createdBy?->name,
                'rejection_reasons' => $this->batchReasons($batch, SubmissionBatch::STATUS_REJECTED),
                'comments' => $this->batchComments($batch),
            ])
            ->values()
            ->all();
    }

    public function getTimeline()
    {
        $workflowInstanceId = $this->workflowInstanceId();

        if ($workflowInstanceId) {
            $history = DB::table('workflow_histories')
                ->leftJoin('workflow_status as previous_status', 'previous_status.id', '=', 'workflow_histories.status_sebelumnya')
                ->leftJoin('workflow_status as new_status', 'new_status.id', '=', 'workflow_histories.status_baru')
                ->leftJoin('users', 'users.id', '=', 'workflow_histories.dilakukan_oleh')
                ->where('workflow_histories.workflow_instance_id', $workflowInstanceId)
                ->orderBy('workflow_histories.created_at')
                ->get([
                    'previous_status.nama as status_sebelumnya',
                    'new_status.nama as status_baru',
                    'workflow_histories.catatan',
                    'workflow_histories.alasan',
                    'workflow_histories.created_at',
                    'users.name as dilakukan_oleh',
                ]);

            if ($history->isNotEmpty()) {
                return $history->map(fn ($item): array => [
                    'waktu' => \Illuminate\Support\Carbon::parse($item->created_at),
                    'status_sebelumnya' => $item->status_sebelumnya,
                    'status_baru' => $item->status_baru,
                    'catatan' => $item->catatan ?? $item->alasan,
                    'dilakukan_oleh' => $item->dilakukan_oleh,
                ])->values();
            }
        }

        return collect([
            [
                'jenis' => 'batch',
                'waktu' => $this->record->created_at,
                'status_baru' => 'Draft',
                'catatan' => 'Batch dibuat',
                'dilakukan_oleh' => $this->record->createdBy?->name,
            ],
            [
                'waktu' => $this->record->submitted_at,
                'status_baru' => SubmissionBatch::statusLabel(SubmissionBatch::STATUS_PENDING),
                'catatan' => 'Batch dikirim untuk diverifikasi',
                'dilakukan_oleh' => $this->record->createdBy?->name,
            ],
            [
                'waktu' => $this->record->verified_at,
                'status_baru' => SubmissionBatch::statusLabel($this->record->status),
                'catatan' => $this->record->notes,
                'dilakukan_oleh' => $this->record->verifiedBy?->name,
            ],
        ])->filter(fn (array $item): bool => ! empty($item['waktu']))->values();
    }

    private function workflowInstanceId(): ?int
    {
        return DB::table('workflow_instances')
            ->where('submission_batch_id', $this->record->id)
            ->value('id');
    }

    private function versionContainsSameModulePeriod(SubmissionBatch $batch, string $module): bool
    {
        $snapshot = $this->moduleSnapshotFor($batch, $module);
        $currentSnapshot = $this->moduleSnapshotFor($this->record, $module);

        if (! $snapshot || ! $currentSnapshot) {
            return false;
        }

        return ($snapshot['periode_bulan'] ?? null) === ($currentSnapshot['periode_bulan'] ?? null)
            && ($snapshot['semester'] ?? null) === ($currentSnapshot['semester'] ?? null)
            && ($snapshot['periode_tahun'] ?? null) === ($currentSnapshot['periode_tahun'] ?? null);
    }

    private function moduleSnapshotFor(SubmissionBatch $batch, string $module): ?array
    {
        $snapshot = $batch->payload_snapshot ?? [];

        if (($snapshot['module'] ?? null) === $module) {
            return $snapshot;
        }

        return collect($snapshot['modules'] ?? [])->firstWhere('module', $module);
    }

    private function latestActiveVersionId($modules): ?int
    {
        return SubmissionBatch::query()
            ->where('kecamatan_id', $this->record->kecamatan_id)
            ->where('package_signature', implode('+', collect($modules)->sort()->values()->all()))
            ->whereIn('status', [
                SubmissionBatch::STATUS_PENDING,
                SubmissionBatch::STATUS_IN_REVIEW,
                SubmissionBatch::STATUS_APPROVED,
            ])
            ->orderByDesc('id')
            ->value('id');
    }

    private function batchReasons(SubmissionBatch $batch, string $status): array
    {
        $workflowInstanceId = DB::table('workflow_instances')
            ->where('submission_batch_id', $batch->id)
            ->value('id');

        if (! $workflowInstanceId) {
            return [];
        }

        return DB::table('workflow_histories')
            ->join('workflow_status', 'workflow_status.id', '=', 'workflow_histories.status_baru')
            ->where('workflow_histories.workflow_instance_id', $workflowInstanceId)
            ->where('workflow_status.kode', $status)
            ->orderBy('workflow_histories.created_at')
            ->pluck('workflow_histories.alasan')
            ->filter()
            ->values()
            ->all();
    }

    private function batchComments(SubmissionBatch $batch): array
    {
        $workflowInstanceId = DB::table('workflow_instances')
            ->where('submission_batch_id', $batch->id)
            ->value('id');

        if (! $workflowInstanceId) {
            return [];
        }

        return DB::table('verification_comments')
            ->where('workflow_instance_id', $workflowInstanceId)
            ->orderBy('created_at')
            ->pluck('komentar')
            ->filter()
            ->values()
            ->all();
    }

    private function summarizeItem(SubmissionBatchItem $item): array
    {
        $entity = $item->entity;
        $module = $item->package_key ?? $this->record->module;

        return match (true) {
            $entity instanceof Rumah => [
                'module' => $module,
                'type' => 'Rumah',
                'title' => $entity->kode_rumah ?? '-',
                'subtitle' => $entity->alamat_lengkap ?? '-',
                'fields' => [
                    ['label' => 'Desa', 'value' => $entity->desa?->nama],
                    ['label' => 'Koordinat', 'value' => trim(($entity->latitude ?? '').', '.($entity->longitude ?? ''), ', ')],
                    ['label' => 'Status', 'value' => $entity->status_rumah],
                ],
            ],
            $entity instanceof Keluarga => [
                'module' => $module,
                'type' => 'Keluarga',
                'title' => $entity->nomor_kk ?? '-',
                'subtitle' => $entity->nama_kepala_keluarga ?? '-',
                'fields' => [
                    ['label' => 'Rumah', 'value' => $entity->rumah?->kode_rumah],
                    ['label' => 'Desa', 'value' => $entity->rumah?->desa?->nama],
                    ['label' => 'Jumlah Anggota', 'value' => $entity->jumlah_anggota],
                ],
            ],
            $entity instanceof Penduduk => [
                'module' => $module,
                'type' => 'Penduduk',
                'title' => $entity->nama_lengkap ?? '-',
                'subtitle' => $entity->nik ?? '-',
                'fields' => [
                    ['label' => 'KK', 'value' => $entity->keluarga?->nomor_kk],
                    ['label' => 'Rumah', 'value' => $entity->keluarga?->rumah?->kode_rumah],
                    ['label' => 'Pendidikan', 'value' => $entity->pendidikan],
                    ['label' => 'Pekerjaan', 'value' => $entity->pekerjaan],
                ],
            ],
            $entity instanceof DataPendidikanBulanan => [
                'module' => $module,
                'type' => 'Pendidikan',
                'title' => $entity->sekolah ?? '-',
                'subtitle' => 'Semester '.($entity->semester ?? '-').' / '.($entity->periode_tahun ?? '-'),
                'fields' => [
                    ['label' => 'Kecamatan', 'value' => $entity->kecamatan?->nama],
                    ['label' => 'Desa', 'value' => $entity->desa?->nama],
                    ['label' => 'Jumlah Siswa', 'value' => $entity->jumlah_siswa],
                    ['label' => 'Status', 'value' => $entity->status],
                ],
            ],
            $entity instanceof DataKesehatanBulanan => [
                'module' => $module,
                'type' => 'Kesehatan',
                'title' => $entity->puskesmas ?? '-',
                'subtitle' => 'Bulan '.($entity->periode_bulan ?? '-').' / '.($entity->periode_tahun ?? '-'),
                'fields' => [
                    ['label' => 'Kecamatan', 'value' => $entity->kecamatan?->nama],
                    ['label' => 'Desa', 'value' => $entity->desa?->nama],
                    ['label' => 'Jumlah Balita', 'value' => $entity->jumlah_balita],
                    ['label' => 'Status', 'value' => $entity->status],
                ],
            ],
            default => [
                'module' => $module,
                'type' => class_basename($entity),
                'title' => $entity?->getKey() ?? '-',
                'subtitle' => '-',
                'fields' => [],
            ],
        };
    }
}
