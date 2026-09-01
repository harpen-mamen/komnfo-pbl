<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class IncomeDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Penghasilan Penduduk';
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        $incomeRanges = [
            '< 1 Juta' => [0, 1000000],
            '1-2 Juta' => [1000000, 2000000],
            '2-3 Juta' => [2000000, 3000000],
            '3-5 Juta' => [3000000, 5000000],
            '5-10 Juta' => [5000000, 10000000],
            '> 10 Juta' => [10000000, PHP_INT_MAX],
        ];

        $incomeData = [];
        $labels = [];

        foreach ($incomeRanges as $label => $range) {
            $count = Penduduk::query()
                ->visibleToCurrentUser()
                ->inApprovedBatch()
                ->whereBetween('penghasilan', $range)
                ->count();
            
            $incomeData[] = $count;
            $labels[] = $label;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Penduduk',
                    'data' => $incomeData,
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(251, 146, 60, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                    ],
                    'borderColor' => ['#dc2626', '#ea580c', '#f97316', '#16a34a', '#2563eb', '#7c3aed'],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
