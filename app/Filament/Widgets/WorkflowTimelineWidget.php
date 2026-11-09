<?php

namespace App\Filament\Widgets;

use App\Models\WORKFLOW\WorkflowLog;
use App\Support\UserScope;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * WorkflowTimelineWidget — 8 transisi workflow terbaru.
 * Scope-aware. Polling 60s. Untuk admin/walidata tampilkan global; role lain scoped.
 */
class WorkflowTimelineWidget extends Widget
{
    protected string $view = 'filament.widgets.workflow-timeline';
    protected int|string|array $columnSpan = 'full';

    /** @var array<int,array<string,mixed>> */
    public array $logs = [];

    public function mount(): void
    {
        $u = Filament::auth()->user();
        if (! $u) {
            return;
        }

        $this->logs = UserScope::applyToUnitId(WorkflowLog::query(), $u)
            ->with(['aktor:id,nama', 'batch:id,periode,unit_id', 'batch.unit:id,nama'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($l) => [
                'id'           => $l->id,
                'batch_id'     => $l->batch_id,
                'periode'      => $l->batch?->periode ?? '-',
                'unit'         => $l->batch?->unit?->nama ?? '-',
                'dari_status'  => $l->dari_status,
                'ke_status'    => $l->ke_status,
                'aktor'        => $l->aktor?->nama ?? '-',
                'catatan'      => $l->catatan,
                'time'         => $l->created_at?->diffForHumans(Carbon::now(), ['short' => true]) ?? '-',
                'time_full'    => $l->created_at?->format('d M Y H:i') ?? '-',
            ])
            ->toArray();
    }
}
