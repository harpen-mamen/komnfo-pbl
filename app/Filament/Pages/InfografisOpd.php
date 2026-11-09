<?php

namespace App\Filament\Pages;

use App\Services\InfographicPresentationService;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Services\DynamicChartService;
use App\Services\DatasetSpatialDataService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\RouteParameter;

class InfografisOpd extends Page
{
    protected static ?string $slug = 'infografis-opd/{opd}';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Infografis OPD';
    protected string $view = 'filament.pages.infografis-opd';
    protected static bool $shouldRegisterNavigation = false;

    public ?Opd $opdModel = null;
    public array $visualDatasets = [];
    public string $selectedYear = '';
    public array $availableYears = [];

    public function mount(#[RouteParameter] string $opd = ''): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $this->opdModel = Opd::query()->where('aktif', true)->findOrFail($opd);
        $canViewAll = $user->hasAnyRole(['super_admin', 'admin', 'admin_kominfo', 'walidata']);
        abort_unless($canViewAll || (int) $user->opd_id === (int) $this->opdModel->id, 403);
        $this->visualDatasets = $this->buildVisualDatasets();
        $this->availableYears = collect($this->visualDatasets)->flatMap(fn ($item) => $item['context']['years'])->unique()->sortDesc()->values()->all();
    }

    public function updatedSelectedYear(): void
    {
        abort_unless($this->selectedYear === '' || in_array((int)$this->selectedYear, array_map('intval',$this->availableYears),true), 422);
        $this->visualDatasets = $this->buildVisualDatasets();
    }

    public function getViewData(): array
    {
        return ['opd' => $this->opdModel, 'datasets' => $this->visualDatasets];
    }

    private function buildVisualDatasets(): array
    {
        $chartService = app(DynamicChartService::class);
        $spatialService = app(DatasetSpatialDataService::class);
        $query = Dataset::query()
            ->with('fields')
            ->with(['values' => fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('published_at')
                ->when($this->selectedYear !== '', fn ($query) => $query->where('tahun', (int)$this->selectedYear))
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-INFOGRAFIS]%'))
                ->orderBy('tahun')->orderBy('id')
                ->select(['id', 'dataset_id', 'tahun', 'periode_label', 'values', 'notes'])])
            ->withCount(['values as published_count' => fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->whereNotNull('published_at')
                ->when($this->selectedYear !== '', fn ($query) => $query->where('tahun', (int)$this->selectedYear))
                ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-INFOGRAFIS]%'))])
            ->whereIn('opd_id', $this->visibleOpdIds())
            ->where('is_active', true)
            ->orderBy('nama');

        // Render datasets with published data. Empty datasets are represented by
        // the empty-state card only when the OPD has no active datasets at all.
        $datasets = $query->get()->map(function (Dataset $dataset) use ($chartService, $spatialService): array {
            $records = $dataset->values;

            return [
                'dataset' => $dataset,
                'charts' => $chartService->statisticsForDataset($dataset, $records),
                'published_count' => (int) $dataset->published_count,
                'map_features' => $spatialService->featuresForDataset($dataset, $records),
                'context' => app(InfographicPresentationService::class)->context($dataset, $records),
            ];
        })->filter(fn (array $item) => $item['published_count'] > 0)->values();
        return $datasets->all();
    }

    private function visibleOpdIds(): array
    {
        $user = Auth::user();
        if ($user?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo', 'walidata'])) {
            return Opd::query()->where('aktif', true)->where('nama', $this->opdModel->nama)->pluck('id')->all();
        }

        return [(int) $this->opdModel->id];
    }
}
