<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class GenderCompositionWidget extends ChartWidget
{
    protected ?string $heading = 'Komposisi Gender Penduduk';
    protected static ?int $sort = 9;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $male = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch()->where('jenis_kelamin', 'Laki-laki')->count();
        $female = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch()->where('jenis_kelamin', 'Perempuan')->count();
        $total = $male + $female;

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Penduduk',
                    'data' => [$male, $female],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(236, 72, 153, 0.7)',
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(236, 72, 153, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Laki-laki (' . round(($male / $total) * 100, 1) . '%)',
                'Perempuan (' . round(($female / $total) * 100, 1) . '%)',
            ],
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
                        'font' => ['size' => 13, 'weight' => 'bold'],
                    ],
                ],
            ],
        ];
    }
}
