<?php

namespace App\Filament\Widgets;

use App\Models\DATA_LAPANGAN\ObjekLapangan;
use App\Enums\BatchStatus;
use App\Models\DATA_SEKTORAL\Dataset;
use App\Models\User;
use App\Models\WORKFLOW\SubmissionBatch;
use App\Support\UserScope;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * PendingTasksWidget — Antrean aksi sesuai role.
 * Tiap tier role punya set tasks berbeda:
 * - kabupaten (walidata/pembina): verifikasi + publish + forum
 * - opd (admin/operator/kepala): draft saya + approval internal
 * - kecamatan: monitoring progress desa
 * - desa: input objek & sync
 *
 * Polling 60s (cukup responsif, tidak berat).
 */
class PendingTasksWidget extends Widget
{
    protected string $view = 'filament.widgets.pending-tasks';
    protected int|string|array $columnSpan = 'full';

    /** @var array<string,array<int,array{label:string,count:int,url:string,color:string,icon:string}>> */
    public array $tasks = [];
    public string $emptyMessage = '';

    public function mount(): void
    {
        $u = Filament::auth()->user();
        if (! $u) {
            $this->tasks = [];
            $this->emptyMessage = 'Tidak ada user yang login.';
            return;
        }

        $this->tasks = match ($u->role?->tier()) {
            'kabupaten' => $this->kabupatenTasks($u),
            'opd'       => $this->opdTasks($u),
            'kecamatan' => $this->kecamatanTasks($u),
            'desa'      => $this->desaTasks($u),
            default     => [],
        };
    }

    // ---------- per-role ----------

    private function kabupatenTasks(User $u): array
    {
        $verifyQ = UserScope::applyToUnitId(SubmissionBatch::query(), $u)
            ->where('status', BatchStatus::SUBMITTED->value);
        $publishQ = UserScope::applyToUnitId(SubmissionBatch::query(), $u)
            ->where('status', BatchStatus::VERIFIED->value);
        $forumQ = Dataset::where('status_verifikasi_forum', 'belum');

        return [
            'primary' => [
                ['label' => 'Verifikasi submission', 'count' => $verifyQ->count(),
                 'url' => '/admin/submission-batches?tableFilters[status][value]=submitted',
                 'color' => '#F59E0B', 'icon' => 'check'],
                ['label' => 'Publish batch', 'count' => $publishQ->count(),
                 'url' => '/admin/submission-batches?tableFilters[status][value]=verified',
                 'color' => '#10B981', 'icon' => 'globe'],
            ],
            'secondary' => [
                ['label' => 'Forum verifikasi metadata', 'count' => $forumQ->count(),
                 'url' => '/admin/datasets?tableFilters[status_verifikasi_forum][value]=belum',
                 'color' => '#8B5CF6', 'icon' => 'shield'],
            ],
        ];
    }

    private function opdTasks(User $u): array
    {
        $draftQ = SubmissionBatch::where('status', BatchStatus::DRAFT->value)
            ->where('diajukan_oleh', $u->id);
        $pendingApprQ = SubmissionBatch::where('status', BatchStatus::APPROVED_INTERNAL->value)
            ->where('unit_id', $u->unit_id);
        $inRevisionQ = SubmissionBatch::where('status', BatchStatus::REVISION->value)
            ->where('unit_id', $u->unit_id);

        return [
            'primary' => [
                ['label' => 'Submit draft saya', 'count' => $draftQ->count(),
                 'url' => '/admin/submission-batches?tableFilters[status][value]=draft',
                 'color' => '#3B82F6', 'icon' => 'upload'],
                ['label' => 'Approve internal', 'count' => $pendingApprQ->count(),
                 'url' => '/admin/submission-batches?tableFilters[status][value]=approved_internal',
                 'color' => '#F59E0B', 'icon' => 'thumb-up'],
            ],
            'secondary' => [
                ['label' => 'Revisi dari walidata', 'count' => $inRevisionQ->count(),
                 'url' => '/admin/submission-batches?tableFilters[status][value]=revision',
                 'color' => '#F97316', 'icon' => 'edit'],
            ],
        ];
    }

    private function kecamatanTasks(User $u): array
    {
        // Batches dari desa-desa dalam scope kecamatan (di-handle oleh UserScope::applyToUnitId)
        $total = UserScope::applyToUnitId(SubmissionBatch::query(), $u)->count();
        $published = UserScope::applyToUnitId(SubmissionBatch::query(), $u)
            ->where('status', BatchStatus::PUBLISHED->value)->count();
        $pending = UserScope::applyToUnitId(SubmissionBatch::query(), $u)
            ->whereIn('status', [BatchStatus::DRAFT->value, BatchStatus::SUBMITTED->value, BatchStatus::APPROVED_INTERNAL->value])->count();

        $compliance = $total > 0 ? (int) round(($published / $total) * 100) : 0;

        return [
            'primary' => [
                ['label' => 'Batch perlu approval', 'count' => $pending,
                 'url' => '/admin/submission-batches',
                 'color' => '#8B5CF6', 'icon' => 'clipboard'],
            ],
            'secondary' => [
                ['label' => 'Compliance published', 'count' => $compliance,
                 'url' => '/admin/submission-batches',
                 'color' => '#10B981', 'icon' => 'check-circle'],
            ],
        ];
    }

    private function desaTasks(User $u): array
    {
        $draftObjek = ObjekLapangan::where('status', 'draft')
            ->where('unit_id', $u->unit_id)->count();
        $submittedObjek = ObjekLapangan::whereIn('status', ['submitted', 'verified'])
            ->where('unit_id', $u->unit_id)->count();

        return [
            'primary' => [
                ['label' => 'Input objek lapangan', 'count' => $draftObjek + $submittedObjek,
                 'url' => '/admin/objek-lapangans/create',
                 'color' => '#1c4ed8', 'icon' => 'plus'],
            ],
            'secondary' => [
                ['label' => 'Objek perlu sinkron', 'count' => $draftObjek,
                 'url' => '/admin/objek-lapangans?tableFilters[status][value]=draft',
                 'color' => '#F59E0B', 'icon' => 'refresh'],
            ],
        ];
    }
}
