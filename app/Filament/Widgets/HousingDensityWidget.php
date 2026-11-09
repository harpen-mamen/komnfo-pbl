<?php

namespace App\Filament\Widgets;

use App\Models\Rumah;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class HousingDensityWidget extends ChartWidget
{
    protected ?string $heading = 'Kepadatan Rumah per Desa';
    protected static ?int $sort = 8;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $data = Rumah::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->with('desa')
            ->get()
            ->groupBy('desa.nama')
            ->map(fn ($houses) => [
                'rumah' => $houses->count(),
                'kk' => $houses->sum('keluarga_count'),
            ])
            ->sort()
            ->take(12);

        return [
            'datasets' => [
                [
                    'label' => 'Rumah',
                    'data' => $data->pluck('rumah')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Keluarga',
                    'data' => $data->pluck('kk')->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.6)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->keys()->toArray(),
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
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'position' => 'top',
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
