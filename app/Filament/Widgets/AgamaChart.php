<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class AgamaChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Agama';

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    protected function getData(): array
    {
        return $this->groupedChart('agama');
    }

    protected function groupedChart(string $column): array
    {
        $data = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch()->selectRaw("{$column}, COUNT(*) as total")->groupBy($column)->pluck('total', $column);

        return ['datasets' => [['label' => 'Jumlah', 'data' => $data->values()]], 'labels' => $data->keys()];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
