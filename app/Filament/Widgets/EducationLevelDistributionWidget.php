<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class EducationLevelDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Tingkat Pendidikan';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $education = Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->groupBy('pendidikan')
            ->selectRaw('pendidikan, COUNT(*) as count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Penduduk',
                    'data' => $education->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                        '#8b5cf6', '#ec4899', '#06b6d4', '#f97316',
                        '#14b8a6', '#6366f1'
                    ],
                    'borderColor' => '#fff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $education->pluck('pendidikan')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 15,
                        'font' => ['size' => 12],
                    ],
                ],
            ],
        ];
    }
}
