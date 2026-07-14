<?php

namespace App\Filament\Widgets;

use App\Models\Desa;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class KominfoWilayahAggregationChart extends ChartWidget
{
    protected static ?string $heading = 'Agregasi Data per Desa';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    protected function getData(): array
    {
        $desas = Desa::query()
            ->withCount([
                'rumah',
                'rumah as keluarga_count' => fn ($query) => $query->join('keluarga', 'keluarga.rumah_id', '=', 'rumah.id'),
                'rumah as penduduk_count' => fn ($query) => $query
                    ->join('keluarga', 'keluarga.rumah_id', '=', 'rumah.id')
                    ->join('penduduk', 'penduduk.keluarga_id', '=', 'keluarga.id'),
            ])
            ->orderBy('nama')
            ->get();

        return [
            'datasets' => [
                ['label' => 'Rumah', 'data' => $desas->pluck('rumah_count')],
                ['label' => 'KK', 'data' => $desas->pluck('keluarga_count')],
                ['label' => 'Penduduk', 'data' => $desas->pluck('penduduk_count')],
            ],
            'labels' => $desas->pluck('nama'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
