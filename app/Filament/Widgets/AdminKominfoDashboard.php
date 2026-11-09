<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\SubmissionBatch;
use App\Models\Wilayah;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AdminKominfoDashboard extends Widget
{
    protected static ?string $heading = 'Dashboard Admin Kominfo';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel();
    }

    protected function getViewData(): array
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // OPD-focused queries
        $opd = Opd::query()->where('aktif', true);
        $dataset = Dataset::query();
        $datasetValue = DatasetValue::query();
        $submissionBatch = SubmissionBatch::query();

        // Stats cards
        $stats = [
            Stat::make('Total OPD', $opd->count())
                ->icon('heroicon-o-building-office')
                ->color('primary'),
            Stat::make('Total Dataset', $dataset->count())
                ->icon('heroicon-o-circle-stack')
                ->color('success'),
            Stat::make('Total Nilai Data', $datasetValue->count())
                ->icon('heroicon-o-table-cells')
                ->color('warning'),
            Stat::make('Kecamatan', Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->count())
                ->icon('heroicon-o-map-pin')
                ->color('info'),
        ];

        // Submission stats (workflow batches)
        $submissionStats = [
            Stat::make('Pending Review', $submissionBatch->where('status', SubmissionBatch::STATUS_PENDING)->count())
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Approved Today', $submissionBatch->whereDate('verified_at', $today)->where('status', SubmissionBatch::STATUS_APPROVED)->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Submission This Month', $submissionBatch->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count())
                ->icon('heroicon-o-calendar')
                ->color('info'),
        ];

        // OPD list with dataset counts
        $opdList = Opd::query()
            ->where('aktif', true)
            ->withCount('datasets')
            ->orderBy('nama')
            ->limit(10)
            ->get()
            ->map(fn ($opd) => [
                'id' => $opd->id,
                'nama' => $opd->nama,
                'datasets_count' => $opd->datasets_count,
            ]);

        return [
            'stats' => $stats,
            'submissionStats' => $submissionStats,
            'opd_list' => $opdList,
            'pending_batches' => $submissionBatch->where('status', SubmissionBatch::STATUS_PENDING)->count(),
            'total_batches' => $submissionBatch->count(),
        ];
    }

    protected function getView(): string
    {
        return 'filament.widgets.admin-kominfo-dashboard';
    }
}
