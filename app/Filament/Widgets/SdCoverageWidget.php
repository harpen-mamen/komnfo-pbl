<?php

namespace App\Filament\Widgets;

use App\Models\DATA_SEKTORAL\Dataset;
use App\Models\Unit;
use Filament\Widgets\Widget;

/**
 * SdCoverageWidget — 4 prinsip Satu Data Indonesia (SDI) dengan progress bar.
 *
 * 1. Standard Data     → dataset dengan kolom_dimensi
 * 2. Metadata          → dataset dengan metadata
 * 3. Kode Referensi    → unit dengan kode_bps (referensi resmi BPS)
 * 4. Interoperabilitas → dataset yang punya record published
 *
 * Cocok dengan 12-col (6 col kalau berdampingan dengan ObjekMapWidget).
 */
class SdCoverageWidget extends Widget
{
    protected string $view = 'filament.widgets.sd-coverage';
    protected int|string|array $columnSpan = 6;

    /** @var array<int,array{label:string,score:int,total:int,note:string,color:string}> */
    public array $principles = [];

    public function mount(): void
    {
        $totalDataset = Dataset::where('aktif', 1)->count();
        $withDimensi = Dataset::where('aktif', 1)->whereNotNull('kolom_dimensi')->count();
        $withMetadata = Dataset::where('aktif', 1)->whereNotNull('metadata')->count();
        $totalUnit = Unit::where('aktif', 1)->count();
        $withBpsCode = Unit::where('aktif', 1)->whereNotNull('kode_bps')->count();
        $withPublished = Dataset::where('aktif', 1)
            ->whereHas('records', fn ($q) => $q->whereHas('batch', fn ($q2) => $q2->where('status', 'published')))
            ->count();

        $pct = fn (int $n, int $total): int => $total > 0 ? (int) round(($n / $total) * 100) : 0;

        $this->principles = [
            [
                'label'  => 'Standard Data',
                'score'  => $pct($withDimensi, $totalDataset),
                'total'  => $totalDataset,
                'count'  => $withDimensi,
                'note'   => "{$withDimensi} dari {$totalDataset} dataset memiliki kolom dimensi",
                'color'  => '#1c4ed8',
                'icon'   => 'table',
            ],
            [
                'label'  => 'Metadata',
                'score'  => $pct($withMetadata, $totalDataset),
                'total'  => $totalDataset,
                'count'  => $withMetadata,
                'note'   => "{$withMetadata} dari {$totalDataset} dataset memiliki metadata terstruktur",
                'color'  => '#10B981',
                'icon'   => 'document-text',
            ],
            [
                'label'  => 'Kode Referensi',
                'score'  => $pct($withBpsCode, $totalUnit),
                'total'  => $totalUnit,
                'count'  => $withBpsCode,
                'note'   => "{$withBpsCode} dari {$totalUnit} unit memiliki kode BPS (referensi resmi)",
                'color'  => '#F59E0B',
                'icon'   => 'hashtag',
            ],
            [
                'label'  => 'Interoperabilitas',
                'score'  => $pct($withPublished, $totalDataset),
                'total'  => $totalDataset,
                'count'  => $withPublished,
                'note'   => "{$withPublished} dari {$totalDataset} dataset sudah dipublikasi & interoperable",
                'color'  => '#8B5CF6',
                'icon'   => 'globe',
            ],
        ];
    }
}
