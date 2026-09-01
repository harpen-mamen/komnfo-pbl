<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class MaritalStatusAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Status Perkawinan Penduduk';
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $maritalStatus = Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->groupBy('status_perkawinan')
            ->selectRaw('status_perkawinan, COUNT(*) as count')
            ->orderByDesc('count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Penduduk',
                    'data' => $maritalStatus->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                    ],
                    'borderColor' => ['#15803d', '#1e40af', '#7c2d12', '#7f1d1d'],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $maritalStatus->pluck('status_perkawinan')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
