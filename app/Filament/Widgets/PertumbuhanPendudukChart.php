<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class PertumbuhanPendudukChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pertumbuhan Penduduk';

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    protected function getData(): array
    {
        $data = Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->orderBy('created_at')
            ->get(['created_at'])
            ->groupBy(fn (Penduduk $penduduk) => $penduduk->created_at->format('Y'))
            ->map->count();

        return ['datasets' => [['label' => 'Penduduk Baru per Tahun', 'data' => $data->values()]], 'labels' => $data->keys()];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
