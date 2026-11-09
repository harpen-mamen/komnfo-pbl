<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\Wilayah;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;

class KominfoStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isOperatorOpd();
    }

    protected function getCards(): array
    {
        return [
            Stat::make('Total OPD', Opd::query()->where('aktif', true)->count())
                ->icon('heroicon-o-building-office')
                ->color('primary'),
            Stat::make('Total Dataset', Dataset::query()->count())
                ->icon('heroicon-o-circle-stack')
                ->color('success'),
            Stat::make('Total Nilai Data', DatasetValue::query()->count())
                ->icon('heroicon-o-table-cells')
                ->color('warning'),
            Stat::make('Kecamatan', Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->count())
                ->icon('heroicon-o-map-pin')
                ->color('info'),
            Stat::make('Desa', Wilayah::query()->where('tipe', Wilayah::TIPE_DESA)->count())
                ->icon('heroicon-o-map')
                ->color('gray'),
        ];
    }
}
