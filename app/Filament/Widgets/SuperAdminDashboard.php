<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\User;
use App\Models\Wilayah;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\Widget;

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

        // Base counts - OPD focused
        $totalOpd = Opd::query()->where('aktif', true)->count();
        $totalDataset = Dataset::query()->count();
        $totalDatasetValue = DatasetValue::query()->count();
        $totalUsers = User::query()->count();
        $totalKecamatan = Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->count();
        $totalDesa = Wilayah::query()->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->count();

        // Stats cards
        $stats = [
            Stat::make('Total OPD', $totalOpd)
                ->icon('heroicon-o-building-office')
                ->color('primary'),
            Stat::make('Total Dataset', $totalDataset)
                ->icon('heroicon-o-circle-stack')
                ->color('success'),
            Stat::make('Total Nilai Data', $totalDatasetValue)
                ->icon('heroicon-o-table-cells')
                ->color('warning'),
            Stat::make('Total Kecamatan', $totalKecamatan)
                ->icon('heroicon-o-map-pin')
                ->color('info'),
            Stat::make('Total Desa', $totalDesa)
                ->icon('heroicon-o-map')
                ->color('primary'),
            Stat::make('Total Users', $totalUsers)
                ->icon('heroicon-o-users')
                ->color('success'),
        ];

        // OPD list
        $opdList = Opd::query()
            ->where('aktif', true)
            ->withCount('datasets')
            ->orderBy('nama')
            ->limit(15)
            ->get()
            ->map(fn ($opd) => [
                'id' => $opd->id,
                'nama' => $opd->nama,
                'datasets_count' => $opd->datasets_count,
            ]);

        return [
            'stats' => $stats,
            'opd_list' => $opdList,
        ];
    }

    protected function getView(): string
    {
        return 'filament.widgets.super-admin-dashboard';
    }
}
