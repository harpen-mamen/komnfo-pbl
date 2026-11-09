<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Wilayah;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AdminKecamatanDashboard extends Widget
{
    protected static ?string $heading = 'Dashboard Admin Kecamatan';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    protected function getViewData(): array
    {
        $user = PanelAccess::user();
        $kecamatanId = $user?->kecamatan_id ?: $user?->desa?->parent_id;
        $kecamatan = $kecamatanId ? Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->find($kecamatanId) : null;
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $desa = Wilayah::query()->where('parent_id', $kecamatanId)->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true);

        // Stats cards
        $stats = [
            Stat::make('Total Desa', (clone $desa)->count())
                ->icon('heroicon-o-map')
                ->color('info'),
        ];

        // Submission stats
        $submissionStats = [
            Stat::make('Draft Submission', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->where('status', SubmissionBatch::STATUS_DRAFT)
                ->count())
                ->icon('heroicon-o-pencil')
                ->color('gray'),
            Stat::make('Submitted', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->whereIn('status', [
                    SubmissionBatch::STATUS_PENDING,
                    SubmissionBatch::STATUS_IN_REVIEW,
                    SubmissionBatch::STATUS_REVISION
                ])
                ->count())
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Rejected', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->where('status', SubmissionBatch::STATUS_REJECTED)
                ->count())
                ->icon('heroicon-o-x-mark')
                ->color('danger'),
            Stat::make('Approved', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->where('status', SubmissionBatch::STATUS_APPROVED)
                ->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];

        // Completion percentage
        $totalBatches = SubmissionBatch::query()->where('kecamatan_id', $kecamatanId)->count();
        $approvedBatches = SubmissionBatch::query()->where('kecamatan_id', $kecamatanId)->where('status', SubmissionBatch::STATUS_APPROVED)->count();
        $completionPercentage = $totalBatches > 0 ? round(($approvedBatches / $totalBatches) * 100) : 0;

        $stats[] = Stat::make('Completion Percentage', $completionPercentage . '%')
            ->icon('heroicon-o-chart-pie')
            ->color($completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger'));

        // Monthly submission chart
        $monthlyData = SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(submitted_at, '%b %Y') as label, COUNT(*) as total")
            ->groupByRaw("YEAR(submitted_at), MONTH(submitted_at), DATE_FORMAT(submitted_at, '%b %Y')")
            ->orderByRaw('YEAR(submitted_at), MONTH(submitted_at)')
            ->pluck('total', 'label')
            ->all();

        $labels = collect(range(5, 0, -1))->map(fn (int $offset) => now()->subMonths($offset)->translatedFormat('M Y'))->all();
        $chartData = array_combine($labels, array_map(fn ($label) => (int)($monthlyData[$label] ?? 0), $labels));

        $chart = [
            'type' => 'line',
            'data' => ['datasets' => [['label' => 'Submissions per Bulan', 'data' => array_values($chartData)]], 'labels' => array_keys($chartData)],
        ];

        // Latest submissions
        $latestSubmissions = SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Quick actions URLs
        $quickActions = [
            [
                'label' => 'Buat Submission',
                'description' => 'Buat batch submission baru',
                'url' => filament()->getUrl('resources/submission-batches/create'),
                'color' => 'success',
            ],
        ];

        return [
            'stats' => $stats,
            'submissionStats' => $submissionStats,
            'completionPercentage' => $completionPercentage,
            'chart' => $chart,
            'latestSubmissions' => $latestSubmissions,
            'quickActions' => $quickActions,
            'kecamatan' => $kecamatan,
        ];
    }

    protected function getView(): string
    {
        return 'filament.widgets.admin-kecamatan-dashboard';
    }
}
