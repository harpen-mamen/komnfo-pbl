<?php

namespace App\Filament\Widgets;

use App\Models\Desa;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Kecamatan;
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

class AdminKominfoDashboard extends Widget
{
    protected static ?string $heading = 'Dashboard Admin Kominfo';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::isKominfoKabupaten();
    }

    protected function getViewData(): array
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Base queries
        $kecamatan = Kecamatan::query();
        $desa = Desa::query();
        $rumah = Rumah::query()->inApprovedBatch();
        $keluarga = Keluarga::query()->inApprovedBatch();
        $penduduk = Penduduk::query()->inApprovedBatch();
        $submissionBatch = SubmissionBatch::query();

        // Stats cards
        $stats = [
            Card::make('Total Kecamatan', $kecamatan->count())
                ->icon('heroicon-o-location-marker')
                ->color('primary'),
            Card::make('Total Desa', $desa->count())
                ->icon('heroicon-o-map')
                ->color('success'),
            Card::make('Total Penduduk', $penduduk->count())
                ->icon('heroicon-o-identification')
                ->color('warning'),
            Card::make('Total Rumah', $rumah->count())
                ->icon('heroicon-o-home')
                ->color('info'),
        ];

        // Submission stats
        $submissionStats = [
            Card::make('Pending Review', $submissionBatch->where('status', SubmissionBatch::STATUS_PENDING)->count())
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Card::make('Approved Today', $submissionBatch->whereDate('verified_at', $today)->where('status', SubmissionBatch::STATUS_APPROVED)->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Card::make('Rejected Today', $submissionBatch->whereDate('verified_at', $today)->where('status', SubmissionBatch::STATUS_REJECTED)->count())
                ->icon('heroicon-o-x')
                ->color('danger'),
            Card::make('Submission This Month', $submissionBatch->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->count())
                ->icon('heroicon-o-calendar')
                ->color('info'),
            Card::make('Published Records', $penduduk->count() + $keluarga->count() + $rumah->count())
                ->icon('heroicon-o-document-text')
                ->color('primary'),
        ];

        // Users online (placeholder - would need actual implementation)
        $usersOnline = 0; // This would require tracking active sessions

        $stats[] = Card::make('Users Online', $usersOnline)
            ->icon('heroicon-o-user')
            ->color($usersOnline > 0 ? 'success' : 'gray');

        // Recent activities (placeholder)
        $recentActivities = collect([]); // Would need activity logging implementation

        // Pending queue
        $pendingQueue = $submissionBatch->whereIn('status', [
            SubmissionBatch::STATUS_PENDING,
            SubmissionBatch::STATUS_IN_REVIEW,
            SubmissionBatch::STATUS_REVISION
        ])->orderByDesc('created_at')->take(5)->get();

        // Latest reviews
        $latestReviews = $submissionBatch->whereNotNull('verified_at')
            ->orderByDesc('verified_at')
            ->take(5)
            ->get();

        // Review queue
        $reviewQueue = $submissionBatch->where('status', SubmissionBatch::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();

        // Charts data
        $submissionTrend = $this->getSubmissionTrendData();
        $approvalTrend = $this->getApprovalTrendData();
        $populationByKecamatan = $this->getPopulationByKecamatanData();
        $houseCountByKecamatan = $this->getHouseCountByKecamatanData();

        return [
            'stats' => $stats,
            'submissionStats' => $submissionStats,
            'usersOnline' => $usersOnline,
            'recentActivities' => $recentActivities,
            'pendingQueue' => $pendingQueue,
            'latestReviews' => $latestReviews,
            'reviewQueue' => $reviewQueue,
            'charts' => [
                'submissionTrend' => $submissionTrend,
                'approvalTrend' => $approvalTrend,
                'populationByKecamatan' => $populationByKecamatan,
                'houseCountByKecamatan' => $houseCountByKecamatan,
            ],
        ];
    }

    private function getSubmissionTrendData(): array
    {
        $lastSixMonths = collect(range(5, 0, -1))->map(fn(int $offset) => now()->subMonths($offset));
        $labels = $lastSixMonths->map(fn($date) => $date->translatedFormat('M Y'))->all();
        $chartData = $lastSixMonths->map(fn ($date) => (int) DataKesehatanBulanan::query()->where('periode_tahun', $date->year)->where('periode_bulan', $date->month)->sum('stunting'))->all();

        return [
            'type' => 'line',
            'data' => ['datasets' => [['label' => 'Kasus Stunting', 'data' => array_values($chartData), 'borderColor' => '#dc2626', 'backgroundColor' => 'rgba(220,38,38,.15)', 'fill' => true]], 'labels' => $labels],
        ];
    }

    private function getApprovalTrendData(): array
    {
        $lastSixMonths = collect(range(5, 0, -1))->map(fn(int $offset) => now()->subMonths($offset));
        $labels = $lastSixMonths->map(fn($date) => $date->translatedFormat('M Y'))->all();

        $chartData = $lastSixMonths->map(fn ($date) => (int) DataPendidikanBulanan::query()->where('periode_tahun', $date->year)->where('periode_bulan', $date->month)->sum('putus_sekolah'))->all();

        return [
            'type' => 'line',
            'data' => ['datasets' => [['label' => 'Putus Sekolah', 'data' => array_values($chartData), 'borderColor' => '#d97706', 'backgroundColor' => 'rgba(217,119,6,.15)', 'fill' => true]], 'labels' => $labels],
        ];
    }

    private function getPopulationByKecamatanData(): array
    {
        $data = Kecamatan::query()
            ->selectRaw('kecamatan.nama as label, COUNT(penduduk.id) as total')
            ->leftJoin('desa', 'desa.kecamatan_id', '=', 'kecamatan.id')
            ->leftJoin('rumah', 'rumah.desa_id', '=', 'desa.id')
            ->leftJoin('keluarga', 'keluarga.rumah_id', '=', 'rumah.id')
            ->leftJoin('penduduk', 'penduduk.keluarga_id', '=', 'keluarga.id')
            ->where('penduduk.deleted_at', null)
            ->groupBy('kecamatan.id', 'kecamatan.nama')
            ->orderByDesc('total')
            ->pluck('total', 'label')
            ->all();

        return [
            'type' => 'bar',
            'data' => ['datasets' => [['label' => 'Population by Kecamatan', 'data' => array_values($data)]], 'labels' => array_keys($data)],
        ];
    }

    private function getHouseCountByKecamatanData(): array
    {
        $data = Kecamatan::query()
            ->selectRaw('kecamatan.nama as label, COUNT(rumah.id) as total')
            ->leftJoin('desa', 'desa.kecamatan_id', '=', 'kecamatan.id')
            ->leftJoin('rumah', 'rumah.desa_id', '=', 'desa.id')
            ->where('rumah.deleted_at', null)
            ->groupBy('kecamatan.id', 'kecamatan.nama')
            ->orderByDesc('total')
            ->pluck('total', 'label')
            ->all();

        return [
            'type' => 'bar',
            'data' => ['datasets' => [['label' => 'House Count by Kecamatan', 'data' => array_values($data)]], 'labels' => array_keys($data)],
        ];
    }

    protected function getView(): string
    {
        return 'filament.widgets.admin-kominfo-dashboard';
    }
}
