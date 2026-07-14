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

    protected function getActions(): array
    {
        return [
            Actions\Action::make('startReview')
                ->label('Mulai Verifikasi')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn (): bool => PanelAccess::isKominfoKabupaten()
                    && $this->record->status === SubmissionBatch::STATUS_PENDING)
                ->action(function (): void {
                    app(BatchApprovalService::class)->startReview($this->record, PanelAccess::user());

                    Notification::make()
                        ->title('Verifikasi dimulai')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('approve')
                ->label('Setujui')
                ->color('success')
                ->form([
                    Textarea::make('catatan')
                        ->label('Catatan')
                        ->rows(3),
                ])
                ->requiresConfirmation()
                ->visible(fn (): bool => PanelAccess::isKominfoKabupaten()
                    && $this->record->status === SubmissionBatch::STATUS_IN_REVIEW)
                ->action(function (array $data): void {
                    app(BatchApprovalService::class)->approve($this->record, PanelAccess::user(), $data['catatan'] ?? null);

                    Notification::make()
                        ->title('Batch disetujui')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('requestRevision')
                ->label('Minta Revisi')
                ->color('warning')
                ->form([
                    Textarea::make('catatan')
                        ->label('Alasan')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->visible(fn (): bool => PanelAccess::isKominfoKabupaten()
                    && $this->record->status === SubmissionBatch::STATUS_IN_REVIEW)
                ->action(function (array $data): void {
                    app(BatchApprovalService::class)->requestRevision($this->record, PanelAccess::user(), 'Catatan Kominfo: '.$data['catatan']);

                    Notification::make()
                        ->title('Permintaan revisi terkirim')
                        ->warning()
                        ->send();
                }),
            Actions\Action::make('reject')
                ->label('Tolak')
                ->color('danger')
                ->form([
                    Textarea::make('catatan')
                        ->label('Catatan')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->visible(fn (): bool => PanelAccess::isKominfoKabupaten()
                    && $this->record->status === SubmissionBatch::STATUS_IN_REVIEW)
                ->action(function (array $data): void {
                    app(BatchApprovalService::class)->reject($this->record, PanelAccess::user(), 'Catatan Kominfo: '.$data['catatan']);

                    Notification::make()
                        ->title('Batch ditolak')
                        ->danger()
                        ->send();
                }),
            Actions\Action::make('archive')
                ->label('Arsipkan')
                ->color('gray')
                ->requiresConfirmation()
                ->visible(fn (): bool => PanelAccess::isKominfoKabupaten()
                    && in_array($this->record->status, [SubmissionBatch::STATUS_APPROVED, SubmissionBatch::STATUS_REJECTED], true))
                ->action(function (): void {
                    app(BatchApprovalService::class)->archive($this->record, PanelAccess::user());

                    Notification::make()
                        ->title('Batch diarsipkan')
                        ->success()
                        ->send();
                }),
        ];
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
                'catatan' => 'Batch dikirim ke Kominfo',
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
