<?php

namespace App\Filament\Widgets;

use App\Models\WORKFLOW\SubmissionBatch;
use Filament\Widgets\Widget;

/**
 * RecentBatchesWidget — 5 submission batch terbaru.
 */
class RecentBatchesWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-batches';

    /** Full-width. */
    protected int|string|array $columnSpan = 'full';

    /** @var array<int, array{periode:string,unit:array|null,status:string,created_at:string}> */
    public array $recentBatches = [];

    public function mount(): void
    {
        $this->recentBatches = SubmissionBatch::with('unit')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($batch) {
                return [
                    'periode' => $batch->periode ?? '-',
                    'unit' => $batch->unit ? ['nama' => $batch->unit->nama] : null,
                    'status' => $batch->status->value ?? $batch->status,
                    'created_at' => $batch->created_at->toIso8601String(),
                ];
            })
            ->toArray();
    }
}
