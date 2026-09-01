<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DemographicAnalyticsWidget extends ChartWidget
{
    protected static ?string $heading = 'Analisis Demografis - Piramida Usia';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $penduduk = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch();
        $today = Carbon::today();

        // Age groups
        $ageGroups = [
            '0-5' => ['min' => 0, 'max' => 5],
            '6-10' => ['min' => 6, 'max' => 10],
            '11-15' => ['min' => 11, 'max' => 15],
            '16-20' => ['min' => 16, 'max' => 20],
            '21-30' => ['min' => 21, 'max' => 30],
            '31-40' => ['min' => 31, 'max' => 40],
            '41-50' => ['min' => 41, 'max' => 50],
            '51-60' => ['min' => 51, 'max' => 60],
            '61+' => ['min' => 61, 'max' => 150],
        ];

        $maleData = [];
        $femaleData = [];

        foreach ($ageGroups as $label => $range) {
            $minDate = $today->copy()->subYears($range['max'])->endOfDay();
            $maxDate = $today->copy()->subYears($range['min'])->startOfDay();

            $maleCount = (clone $penduduk)
                ->where('jenis_kelamin', 'Laki-laki')
                ->whereBetween('tanggal_lahir', [$minDate, $maxDate])
                ->count();

            $femaleCount = (clone $penduduk)
                ->where('jenis_kelamin', 'Perempuan')
                ->whereBetween('tanggal_lahir', [$minDate, $maxDate])
                ->count();

            $maleData[] = -$maleCount; // negative for left side
            $femaleData[] = $femaleCount; // positive for right side
        }

        return [
            'datasets' => [
                [
                    'label' => 'Laki-laki',
                    'data' => $maleData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                ],
                [
                    'label' => 'Perempuan',
                    'data' => $femaleData,
                    'borderColor' => '#ec4899',
                    'backgroundColor' => 'rgba(236, 72, 153, 0.5)',
                ],
            ],
            'labels' => array_keys($ageGroups),
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
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => false,
                    'ticks' => [
                        'callback' => 'function(value) { return Math.abs(value); }',
                    ],
                ],
            ],
        ];
    }
}
