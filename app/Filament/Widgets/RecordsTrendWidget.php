<?php

namespace App\Filament\Widgets;

use App\Models\DATA_SEKTORAL\DatasetRecord;
use App\Support\UserScope;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * RecordsTrendWidget — Line chart, jumlah DatasetRecord yang dipublikasi 14 hari terakhir.
 *
 * Scope-aware via UserScope. Default 14 hari (lightweight, cocok untuk polling 30s).
 * Menggunakan Chart.js (CDN, ~50KB) — tanpa dependency Composer baru.
 */
class RecordsTrendWidget extends Widget
{
    protected string $view = 'filament.widgets.records-trend';
    protected int|string|array $columnSpan = 8; // 8 of 12 — left in 2-col grid with timeline

    /** @var array{labels: array<int,string>, data: array<int,int>, total: int, delta: int} */
    public array $trend = ['labels' => [], 'data' => [], 'total' => 0, 'delta' => 0];

    public function mount(): void
    {
        $u = Filament::auth()->user();
        if (! $u) {
            return;
        }

        $end   = Carbon::today();
        $start = $end->copy()->subDays(13); // 14 hari window

        // Hitung per hari di scope user
        $scoped = UserScope::applyToUnitId(DatasetRecord::query(), $u);
        $byDay = (clone $scoped)
            ->whereHas('batch', fn ($q) => $q->where('status', 'published'))
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
            ->groupBy('day')
            ->pluck('c', 'day');

        $labels = [];
        $data = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('d M');
            $data[]   = (int) ($byDay[$cursor->format('Y-m-d')] ?? 0);
            $cursor->addDay();
        }

        $total = array_sum($data);
        $half  = (int) floor(count($data) / 2);
        $first = array_sum(array_slice($data, 0, $half ?: 1));
        $last  = array_sum(array_slice($data, $half ?: 1));
        $delta = $first > 0 ? (int) round((($last - $first) / max($first, 1)) * 100) : 0;

        $this->trend = [
            'labels' => $labels,
            'data'   => $data,
            'total'  => $total,
            'delta'  => $delta,
        ];
    }
}
