<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use App\Models\DATA_SEKTORAL\DatasetRecord;
use App\Models\Unit;
use App\Models\WORKFLOW\SubmissionBatch;
use App\Support\UserScope;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * StatsOverviewWidget — 4 KPI cards di baris paling atas.
 *
 * Auto-polling 30s. Role-aware:
 * - kabupaten: global stats
 * - opd/desa/kecamatan: stats di-scope sesuai UserScope
 */
class StatsOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.stats-overview';

    /** Full-width supaya 4 card jadi 4-col di dalam grid. */
    protected int|string|array $columnSpan = 'full';

    /** @var array<int, array{label:string,value:mixed,icon:string,color:string,subtext?:string}> */
    public array $stats = [];

    public function mount(): void
    {
        $u = Filament::auth()->user();
        if (! $u) {
            return;
        }

        $this->stats = $this->computeStats($u);
    }

    private function computeStats(User $u): array
    {
        $isKabupaten = in_array($u->role?->tier(), ['kabupaten'], true);

        // Helper: query builder yang sudah di-scope, return Builder untuk filter selanjutnya
        $batchScoped = fn () => UserScope::applyToUnitId(SubmissionBatch::query(), $u);
        $recordScoped = fn () => UserScope::applyToUnitId(DatasetRecord::query(), $u);

        // 4 KPI cards
        return [
            [
                'label'   => 'Unit Aktif',
                'value'   => $isKabupaten
                    ? Unit::where('aktif', 1)->count()
                    : Unit::where('aktif', 1)->where('id', $u->unit_id)->count(),
                'icon'    => 'heroicon-o-building-office',
                'color'   => 'primary',
                'subtext' => $isKabupaten ? 'kab + kec + desa + opd' : 'unit Anda',
            ],
            [
                'label'   => 'Record Published',
                'value'   => (clone $recordScoped())
                    ->whereHas('batch', fn ($q) => $q->where('status', 'published'))
                    ->count(),
                'icon'    => 'heroicon-o-document-text',
                'color'   => 'success',
                'subtext' => 'siap diakses publik',
            ],
            [
                'label'   => 'Submission Compliance',
                'value'   => $this->compliancePercent($batchScoped()),
                'icon'    => 'heroicon-o-shield-check',
                'color'   => 'warning',
                'subtext' => '% published / total',
                'isPercent' => true,
            ],
            [
                'label'   => 'Batch Pending',
                'value'   => (clone $batchScoped())
                    ->whereIn('status', ['draft','approved_internal','submitted','verified'])
                    ->count(),
                'icon'    => 'heroicon-o-clock',
                'color'   => 'danger',
                'subtext' => 'menunggu verifikasi',
            ],
        ];
    }

    private function compliancePercent(Builder $q): int
    {
        $total = (clone $q)->count();
        if ($total === 0) {
            return 0;
        }
        $published = (clone $q)->where('status', 'published')->count();
        return (int) round(($published / $total) * 100);
    }
}
