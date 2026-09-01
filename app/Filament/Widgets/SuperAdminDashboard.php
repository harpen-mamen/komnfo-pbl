<?php

namespace App\Filament\Widgets;

use App\Models\AuditTrail;
use App\Models\Desa;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\ImportPendudukLog;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;

class SuperAdminDashboard extends Widget
{
    protected static ?string $heading = 'Dashboard Super Admin';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    protected function getViewData(): array
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Base counts
        $totalKabupaten = Kabupaten::query()->count();
        $totalKecamatan = Kecamatan::query()->count();
        $totalDesa = Desa::query()->count();
        $totalPenduduk = Penduduk::query()->inApprovedBatch()->count();
        $totalRumah = Rumah::query()->inApprovedBatch()->count();
        $totalKeluarga = Keluarga::query()->inApprovedBatch()->count();
        $totalUsers = User::query()->count();
        $totalSubmission = SubmissionBatch::query()->count();
        $totalPublished = Penduduk::query()->inApprovedBatch()->count() + 
                         Keluarga::query()->inApprovedBatch()->count() + 
                         Rumah::query()->inApprovedBatch()->count();

        // Stats cards
        $stats = [
            Card::make('Total Kabupaten', $totalKabupaten)
                ->icon('heroicon-o-office-building')
                ->color('primary'),
            Card::make('Total Kecamatan', $totalKecamatan)
                ->icon('heroicon-o-location-marker')
                ->color('success'),
            Card::make('Total Desa', $totalDesa)
                ->icon('heroicon-o-map')
                ->color('warning'),
            Card::make('Total Penduduk', $totalPenduduk)
                ->icon('heroicon-o-identification')
                ->color('info'),
            Card::make('Total Rumah', $totalRumah)
                ->icon('heroicon-o-home')
                ->color('primary'),
            Card::make('Total KK', $totalKeluarga)
                ->icon('heroicon-o-user-group')
                ->color('success'),
            Card::make('Total Users', $totalUsers)
                ->icon('heroicon-o-users')
                ->color('warning'),
        ];

        // Submission stats
        $submissionStats = [
            Card::make('Total Submission', $totalSubmission)
                ->icon('heroicon-o-document-text')
                ->color('info'),
            Card::make('Total Published Data', $totalPublished)
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];

        // Charts data
        $submissionTrend = $this->getSubmissionTrendData();
        $approvalTrend = $this->getApprovalTrendData();
        $populationByKecamatan = $this->getPopulationByKecamatanData();
        $houseCountByKecamatan = $this->getHouseCountByKecamatanData();

        return [
            'stats' => $stats,
            'submissionStats' => $submissionStats,
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
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $data = Kecamatan::query()
            ->selectRaw('kecamatan.nama as label, COUNT(DISTINCT penduduk.id) as total')
            ->leftJoin('desa', 'desa.kecamatan_id', '=', 'kecamatan.id')
            ->leftJoin('rumah', function ($join) {
                $join->on('rumah.desa_id', '=', 'desa.id')
                    ->whereHas('latestSubmissionBatchItem.submissionBatch', fn ($q) =>
                        $q->where('status', SubmissionBatch::STATUS_APPROVED)
                    );
            })
            ->leftJoin('keluarga', 'keluarga.rumah_id', '=', 'rumah.id')
            ->leftJoin('penduduk', function ($join) {
                $join->on('penduduk.keluarga_id', '=', 'keluarga.id')
                    ->whereHas('latestSubmissionBatchItem.submissionBatch', fn ($q) =>
                        $q->where('status', SubmissionBatch::STATUS_APPROVED)
                    );
            })
            ->groupBy('kecamatan.id', 'kecamatan.nama')
            ->orderByDesc('total')
            ->pluck('total', 'label')
            ->all();

        return [
            'type' => 'bar',
            'data' => ['datasets' => [['label' => 'Population by Kecamatan (Approved)', 'data' => array_values($data)]], 'labels' => array_keys($data)],
        ];
    }

    private function getHouseCountByKecamatanData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $data = Kecamatan::query()
            ->selectRaw('kecamatan.nama as label, COUNT(DISTINCT rumah.id) as total')
            ->leftJoin('desa', 'desa.kecamatan_id', '=', 'kecamatan.id')
            ->leftJoin('rumah', function ($join) {
                $join->on('rumah.desa_id', '=', 'desa.id')
                    ->whereHas('latestSubmissionBatchItem.submissionBatch', fn ($q) =>
                        $q->where('status', SubmissionBatch::STATUS_APPROVED)
                    );
            })
            ->groupBy('kecamatan.id', 'kecamatan.nama')
            ->orderByDesc('total')
            ->pluck('total', 'label')
            ->all();

        return [
            'type' => 'bar',
            'data' => ['datasets' => [['label' => 'House Count by Kecamatan (Approved)', 'data' => array_values($data)]], 'labels' => array_keys($data)],
        ];
    }

    protected function getView(): string
    {
        return 'filament.widgets.super-admin-dashboard';
    }
}
