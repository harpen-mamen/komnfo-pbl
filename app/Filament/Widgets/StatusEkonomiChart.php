<?php

namespace App\Filament\Widgets;

use App\Models\Keluarga;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class StatusEkonomiChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Status Ekonomi';

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    protected function getData(): array
    {
        $data = Keluarga::query()->visibleToCurrentUser()->inApprovedBatch()->selectRaw('status_ekonomi, COUNT(*) as total')->groupBy('status_ekonomi')->pluck('total', 'status_ekonomi');

        return ['datasets' => [['label' => 'Jumlah KK', 'data' => $data->values()]], 'labels' => $data->keys()];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
