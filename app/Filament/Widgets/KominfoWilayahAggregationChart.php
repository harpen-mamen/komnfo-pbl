<?php

namespace App\Filament\Widgets;

use App\Models\Desa;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Filament\Widgets\ChartWidget;

class KominfoWilayahAggregationChart extends ChartWidget
{
    protected ?string $heading = 'Agregasi Data per Desa';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    protected function getData(): array
    {
        // READ MODEL: Only approved data via submission batch joins (Arsitektur §4.3)
        $desas = Desa::query()
            ->withCount([
                // Rumah: join through latest submission batch item → approved batch
                'rumah as rumah_count' => fn ($query) => $query
                    ->whereHas('latestSubmissionBatchItem.submissionBatch', fn ($sb) => $sb->where('status', SubmissionBatch::STATUS_APPROVED)),

                // Keluarga: join through rumah → approved batch
                'rumah.keluarga as keluarga_count' => fn ($query) => $query
                    ->whereHas('rumah.latestSubmissionBatchItem.submissionBatch', fn ($sb) => $sb->where('status', SubmissionBatch::STATUS_APPROVED)),

                // Penduduk: join through keluarga → rumah → approved batch
                'rumah.keluarga.penduduk as penduduk_count' => fn ($query) => $query
                    ->whereHas('rumah.keluarga.latestSubmissionBatchItem.submissionBatch', fn ($sb) => $sb->where('status', SubmissionBatch::STATUS_APPROVED)),
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
