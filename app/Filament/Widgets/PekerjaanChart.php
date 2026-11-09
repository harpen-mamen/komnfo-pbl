<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class PekerjaanChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pekerjaan';

    public static function canView(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    protected function getData(): array
    {
        $data = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch()->selectRaw("COALESCE(NULLIF(pekerjaan, ''), 'Tidak Diisi') as label, COUNT(*) as total")->groupBy('label')->orderByDesc('total')->limit(10)->pluck('total', 'label');

        return ['datasets' => [['label' => 'Jumlah', 'data' => $data->values()]], 'labels' => $data->keys()];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
