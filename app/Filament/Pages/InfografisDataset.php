<?php

namespace App\Filament\Pages;

use App\Services\InfographicPresentationService;
use App\Models\Dataset;
use App\Services\DataInput\DatasetExportService;
use App\Services\DynamicChartService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class InfografisDataset extends Page
{
    protected static ?string $slug = 'infografis-dataset/{kode}';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Infografis Dataset';
    protected string $view = 'filament.pages.infografis-dataset';
    protected static bool $shouldRegisterNavigation = false;

    public string $kode;
    public ?Dataset $dataset = null;

    public function mount(string $kode): void
    {
        $this->kode = $kode;
        $this->dataset = Dataset::with(['opd', 'fields'])->where('kode', $kode)->where('is_active', true)->firstOrFail();
        $user = Auth::user();
        abort_unless($user && ($user->isSuperAdmin() || $user->isWalidata() || (int)$user->opd_id === (int)$this->dataset->opd_id), 403);
    }

    public function getViewData(): array
    {
        $service = app(DatasetExportService::class);
        $published = $this->dataset->values()->where('status', 'published')
            ->whereNotNull('published_at')->orderBy('tahun')->orderBy('id')->get();

        return [
            'dataset' => $this->dataset,
            'opd' => $this->dataset->opd,
            'fields' => $this->dataset->fields->sortBy('urutan'),
            'statistics' => app(DynamicChartService::class)->statisticsForDataset($this->dataset, $published),
            'breakdown' => $service->getStatusBreakdown($this->dataset),
            'total_values' => $this->dataset->values()->count(),
            'published_count' => $published->count(),
            'recent_values' => $this->dataset->values()->latest('created_at')->limit(10)->get(),
            'context' => app(InfographicPresentationService::class)->context($this->dataset, $published),
        ];
    }
}
