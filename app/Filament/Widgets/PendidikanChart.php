<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class PendidikanChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pendidikan';

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    protected function getData(): array
    {
        $data = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch()->selectRaw('pendidikan, COUNT(*) as total')->groupBy('pendidikan')->pluck('total', 'pendidikan');

        return ['datasets' => [['label' => 'Jumlah', 'data' => $data->values()]], 'labels' => $data->keys()];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
