<?php

namespace App\Filament\Widgets;

use App\Models\Desa;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\ChartWidget;
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
        $kecamatanId = $user?->kecamatan_id;
        $kecamatan = $kecamatanId ? Desa::query()->find($kecamatanId) : null;
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Base queries with user scoping
        $rumah = Rumah::query()->visibleToCurrentUser();
        $keluarga = Keluarga::query()->visibleToCurrentUser();
        $penduduk = Penduduk::query()->visibleToCurrentUser();
        $desa = Desa::query()->where('kecamatan_id', $kecamatanId);

        // Stats cards
        $stats = [
            Card::make('Total Penduduk', (clone $penduduk)->count())
                ->icon('heroicon-o-identification')
                ->color('primary'),
            Card::make('Total Keluarga', (clone $keluarga)->count())
                ->icon('heroicon-o-user-group')
                ->color('success'),
            Card::make('Total Rumah', (clone $rumah)->count())
                ->icon('heroicon-o-home')
                ->color('warning'),
            Card::make('Total Desa', (clone $desa)->count())
                ->icon('heroicon-o-map')
                ->color('info'),
        ];

        // Submission stats
        $submissionStats = [
            Card::make('Draft Submission', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->where('status', SubmissionBatch::STATUS_DRAFT)
                ->count())
                ->icon('heroicon-o-pencil')
                ->color('gray'),
            Card::make('Submitted', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->whereIn('status', [
                    SubmissionBatch::STATUS_PENDING,
                    SubmissionBatch::STATUS_IN_REVIEW,
                    SubmissionBatch::STATUS_REVISION
                ])
                ->count())
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Card::make('Rejected', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->where('status', SubmissionBatch::STATUS_REJECTED)
                ->count())
                ->icon('heroicon-o-x')
                ->color('danger'),
            Card::make('Approved', SubmissionBatch::query()
                ->where('kecamatan_id', $kecamatanId)
                ->where('status', SubmissionBatch::STATUS_APPROVED)
                ->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];

        // Completion percentage
        $rumahApproved = (clone $rumah)->inApprovedBatch()->count();
        $keluargaApproved = (clone $keluarga)->inApprovedBatch()->count();
        $pendudukApproved = (clone $penduduk)->inApprovedBatch()->count();
        $rumahTotal = (clone $rumah)->count();
        $keluargaTotal = (clone $keluarga)->count();
        $pendudukTotal = (clone $penduduk)->count();

        $rumahPercentage = $rumahTotal > 0 ? round(($rumahApproved / $rumahTotal) * 100) : 0;
        $keluargaPercentage = $keluargaTotal > 0 ? round(($keluargaApproved / $keluargaTotal) * 100) : 0;
        $pendudukPercentage = $pendudukTotal > 0 ? round(($pendudukApproved / $pendudukTotal) * 100) : 0;

        $completionPercentage = round(($rumahPercentage + $keluargaPercentage + $pendudukPercentage) / 3);

        $stats[] = Card::make('Completion Percentage', $completionPercentage . '%')
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

        $labels = collect(range(5, 0, -1))->map(fn(int $offset) => now()->subMonths($offset)->translatedFormat('M Y'))->all();
        $chartData = array_combine($labels, array_map(fn($label) => (int)($monthlyData[$label] ?? 0), $labels));

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
                'label' => 'Input Penduduk',
                'description' => 'Tambah data penduduk baru',
                'url' => filament()->getUrl('resources/penduduk/create'),
                'color' => 'primary',
            ],
            [
                'label' => 'Input Keluarga',
                'description' => 'Tambah data keluarga baru',
                'url' => filament()->getUrl('resources/keluarga/create'),
                'color' => 'success',
            ],
            [
                'label' => 'Input Rumah',
                'description' => 'Tambah data rumah baru',
                'url' => filament()->getUrl('resources/rumah/create'),
                'color' => 'warning',
            ],
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
