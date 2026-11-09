<?php

namespace App\Filament\Widgets;

use App\Enums\BatchStatus;
use App\Models\WORKFLOW\SubmissionBatch;
use App\Support\UserScope;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * SubmissionStatusWidget — Donut chart distribusi status batch + 5 batch terbaru.
 * Scope-aware via UserScope. Polling 60s (lebih jarang dari KPI karena lebih berat).
 */
class SubmissionStatusWidget extends Widget
{
    protected string $view = 'filament.widgets.submission-status';
    protected int|string|array $columnSpan = 4; // 4 of 12 — right in 2-col grid dengan trend

    /** @var array{counts: array<string,int>, colors: array<string,string>, recent: array<int,array<string,mixed>>} */
    public array $status = ['counts' => [], 'colors' => [], 'recent' => []];

    public function mount(): void
    {
        $u = Filament::auth()->user();
        if (! $u) {
            return;
        }

        // Counts per status (semua batch dalam scope)
        $scoped = UserScope::applyToUnitId(SubmissionBatch::query(), $u);
        $raw = (clone $scoped)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $counts = [];
        foreach (BatchStatus::cases() as $s) {
            $counts[$s->value] = (int) ($raw[$s->value] ?? 0);
        }

        // 5 batch terbaru
        $recent = (clone $scoped)
            ->with('unit:id,nama')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'unit_id', 'periode', 'status', 'jenis', 'created_at'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'unit' => $b->unit?->nama ?? '-',
                'periode' => $b->periode,
                'status' => $b->status instanceof BatchStatus ? $b->status->value : (string) $b->status,
                'status_label' => $b->status instanceof BatchStatus ? $b->status->label() : (string) $b->status,
                'status_color' => $b->status instanceof BatchStatus ? $b->status->color() : 'gray',
                'jenis' => $b->jenis,
                'created_at' => $b->created_at?->format('d M Y H:i'),
            ])->toArray();

        $this->status = [
            'counts' => $counts,
            'colors' => [
                'draft'             => '#9CA3AF',
                'approved_internal' => '#3B82F6',
                'submitted'         => '#F59E0B',
                'verified'          => '#06B6D4',
                'revision'          => '#F97316',
                'rejected'          => '#EF4444',
                'published'         => '#10B981',
            ],
            'recent' => $recent,
        ];
    }
}
