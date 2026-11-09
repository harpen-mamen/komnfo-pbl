<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Models\Wilayah;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;

class OpdStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::isOperatorOpd();
    }

    protected function getCards(): array
    {
        $datasetStats = Dataset::query()
            ->leftJoin('dataset_values', function ($join) {
                $join->on('dataset_values.dataset_id', '=', 'datasets.id')
                    ->where('dataset_values.status', DatasetValue::STATUS_PUBLISHED)
                    ->whereNotNull('dataset_values.published_at');
            })
            ->where('datasets.is_active', true)
            ->whereNull('datasets.deleted_at')
            ->selectRaw('COUNT(DISTINCT datasets.id) as dataset_count, COUNT(dataset_values.id) as value_count')
            ->first();

        return [
            Stat::make('Total OPD', Opd::query()->where('aktif', true)->count())
                ->icon('heroicon-o-building-office')
                ->color('primary'),
            Stat::make('Total Dataset', (int) ($datasetStats->dataset_count ?? 0))
                ->icon('heroicon-o-circle-stack')
                ->color('success'),
            Stat::make('Total Nilai Data', (int) ($datasetStats->value_count ?? 0))
                ->icon('heroicon-o-table-cells')
                ->color('warning'),
            Stat::make('Kecamatan', Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->count())
                ->icon('heroicon-o-map-pin')
                ->color('info'),
        ];
    }
}
