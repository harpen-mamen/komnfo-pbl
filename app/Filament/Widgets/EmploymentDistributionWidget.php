<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class EmploymentDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Pekerjaan Penduduk';
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $employment = Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->where('pekerjaan', '!=', null)
            ->where('pekerjaan', '!=', '')
            ->groupBy('pekerjaan')
            ->selectRaw('pekerjaan, COUNT(*) as count')
            ->orderByDesc('count')
            ->take(15)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Penduduk',
                    'data' => $employment->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $employment->pluck('pekerjaan')->toArray(),
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
