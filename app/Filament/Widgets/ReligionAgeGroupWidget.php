<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Models\Rumah;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ReligionAgeGroupWidget extends ChartWidget
{
    protected static ?string $heading = 'Agama per Kelompok Usia';
    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $religions = Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->distinct('agama')
            ->pluck('agama')
            ->toArray();

        $ageGroups = [
            '0-17' => ['min' => 0, 'max' => 17],
            '18-35' => ['min' => 18, 'max' => 35],
            '36-55' => ['min' => 36, 'max' => 55],
            '56+' => ['min' => 56, 'max' => 150],
        ];

        $datasets = [];
        $colors = [
            'rgba(59, 130, 246, 0.7)',
            'rgba(34, 197, 94, 0.7)',
            'rgba(249, 115, 22, 0.7)',
            'rgba(236, 72, 153, 0.7)',
            'rgba(139, 92, 246, 0.7)',
            'rgba(6, 182, 212, 0.7)',
        ];

        foreach ($religions as $index => $religion) {
            $data = [];
            foreach ($ageGroups as $range) {
                $count = Penduduk::query()
                    ->visibleToCurrentUser()
                    ->inApprovedBatch()
                    ->where('agama', $religion)
                    ->whereRaw("YEAR(FROM_DAYS(DATEDIFF(NOW(), tanggal_lahir))) BETWEEN ? AND ?", 
                        [$range['min'], $range['max']])
                    ->count();
                $data[] = $count;
            }

            $datasets[] = [
                'label' => $religion,
                'data' => $data,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => str_replace('0.7', '1', $colors[$index % count($colors)]),
                'borderWidth' => 1,
            ];
        }

        return [
            'datasets' => $datasets,
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
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                ],
                'x' => [
                    'stacked' => true,
                ],
            ],
        ];
    }
}
