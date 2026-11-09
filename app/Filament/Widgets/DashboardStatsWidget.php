<?php

namespace App\Filament\Widgets;

use App\Models\DATA_SEKTORAL\Dataset;
use App\Models\DATA_SEKTORAL\DatasetRecord;
use App\Models\DATA_LAPANGAN\ObjekLapangan;
use App\Models\MASTER_DATA\Keluarga;
use App\Models\MASTER_DATA\Penduduk;
use App\Models\Unit;
use App\Models\WORKFLOW\SubmissionBatch;
use Filament\Widgets\Widget;

/**
 * DashboardStatsWidget — 8 stat cards untuk dashboard utama.
 */
class DashboardStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-stats';

    /** Full-width. */
    protected int|string|array $columnSpan = 'full';

    /** @var array<int, array{label:string,value:int,icon:string,color:string}> */
    public array $stats = [];

    public function mount(): void
    {
        $this->stats = [
            [
                'label' => 'Total Penduduk',
                'value' => Penduduk::count(),
                'icon' => 'users',
                'color' => 'blue',
            ],
            [
                'label' => 'Total Keluarga',
                'value' => Keluarga::count(),
                'icon' => 'home',
                'color' => 'green',
            ],
            [
                'label' => 'Total Dataset',
                'value' => Dataset::count(),
                'icon' => 'database',
                'color' => 'purple',
            ],
            [
                'label' => 'Batch Published',
                'value' => SubmissionBatch::where('status', 'published')->count(),
                'icon' => 'check',
                'color' => 'emerald',
            ],
            [
                'label' => 'Kecamatan',
                'value' => Unit::where('tipe', 'kecamatan')->count(),
                'icon' => 'map',
                'color' => 'amber',
            ],
            [
                'label' => 'Desa/Kelurahan',
                'value' => Unit::where('tipe', 'desa')->count(),
                'icon' => 'location',
                'color' => 'teal',
            ],
            [
                'label' => 'Data Records',
                'value' => DatasetRecord::count(),
                'icon' => 'document',
                'color' => 'indigo',
            ],
            [
                'label' => 'Objek Lapangan',
                'value' => ObjekLapangan::count(),
                'icon' => 'building',
                'color' => 'rose',
            ],
        ];
    }
}
