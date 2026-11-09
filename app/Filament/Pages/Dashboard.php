<?php

namespace App\Filament\Pages;

use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use App\Support\PanelAccess;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $title = 'Dashboard OPD';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.dashboard-opd';

    public static function shouldRegisterNavigation(): bool
    {
        // Fokus OPD only
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessOpdPanel();
    }

    public static function getNavigationLabel(): string
    {
        return 'Ringkasan';
    }

    public function getTitle(): string
    {
        return 'Ringkasan Satu Data';
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessOpdPanel(), 403);
    }

    public function render(): View
    {
        $requiredTables = ['opds', 'datasets', 'dataset_fields', 'dataset_values', 'dataset_submissions', 'wilayahs', 'notifications'];
        $missingTables = array_values(array_filter($requiredTables, fn (string $table): bool => ! Schema::hasTable($table)));

        if ($missingTables !== []) {
            // Avoid the sidebar/widgets until their schema is available. This
            // reports missing data explicitly rather than showing false zeros.
            return view('filament.pages.database-unavailable', compact('missingTables'))
                ->layout('filament-panels::components.layout.base', ['title' => 'Database belum lengkap']);
        }

        return parent::render();
    }

    protected function getViewData(): array
    {
        return [
            'dashboard' => $this->buildOpdDashboard(),
        ];
    }

    private function buildOpdDashboard(): array
    {
        $user = PanelAccess::user();
        $now = Carbon::now();
        $kecamatanCount = DB::table('wilayahs')->where('tipe', 'kecamatan')->count();
        $canViewAllOpds = $user->hasAnyRole(['super_admin', 'admin', 'admin_kominfo', 'walidata']);
        $opdGroups = Opd::query()
            ->where('aktif', true)
            ->when(! $canViewAllOpds, fn ($query) => $query->where('id', $user->opd_id))
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama'])
            ->groupBy('nama');

        // Some legacy and current OPD codes describe the same organization.
        // Show one card per official display name and combine its dataset counts.
        $opdIds = $opdGroups->flatten(1)->pluck('id')->all();
        $publishedValues = DB::table('dataset_values')
            ->select('dataset_id', DB::raw('COUNT(*) as published_count'))
            ->whereIn('dataset_id', Dataset::query()->select('id')->whereIn('opd_id', $opdIds)->where('is_active', true))
            ->where('status', DatasetValue::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where(fn ($notes) => $notes->whereNull('notes')->orWhere(function ($validNotes) {
                $validNotes->where('notes', 'not like', '[DEMO-%')
                    ->where('notes', '<>', 'Sample data auto-verified')
                    ->where('notes', '<>', 'Sample');
            }))
            ->groupBy('dataset_id');
        $datasetStats = Dataset::query()
            ->leftJoinSub($publishedValues, 'published_values', fn ($join) => $join->on('published_values.dataset_id', '=', 'datasets.id'))
            ->whereIn('datasets.opd_id', $opdIds)
            ->where('datasets.is_active', true)
            ->whereNull('datasets.deleted_at')
            ->groupBy('datasets.opd_id')
            ->selectRaw('datasets.opd_id, COUNT(*) as dataset_count, COALESCE(SUM(published_values.published_count), 0) as published_count')
            ->get()
            ->keyBy('opd_id');

        $opds = $opdGroups->map(function ($group) use ($datasetStats) {
                $ids = $group->pluck('id');
                $stats = $datasetStats->only($ids->all());

                return [
                    'id' => $group->first()->id,
                    'kode' => $group->pluck('kode')->filter()->unique()->implode(' / '),
                    'nama' => $group->first()->nama,
                    'jumlah_dataset' => (int) $stats->sum('dataset_count'),
                    'jumlah_terbit' => (int) $stats->sum('published_count'),
                ];
            })
            ->sortBy('nama')->values();

        // Get recent datasets
        $recentDatasets = Dataset::query()
            ->select(['id', 'opd_id', 'nama', 'created_at'])
            ->with('opd:id,nama')
            ->whereIn('opd_id', $opdIds)
            ->withCount(['fields', 'values as jumlah_nilai' => fn ($query) => $query->where('status', DatasetValue::STATUS_PUBLISHED)->whereNotNull('published_at')])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($ds) => [
                'nama' => $ds->nama,
                'opd' => $ds->opd?->nama ?? '-',
                'jumlah_field' => $ds->fields_count,
                'jumlah_nilai' => $ds->jumlah_nilai,
            ]);

        return [
            'mode' => 'opd',
            'header' => [
                'title' => 'Dashboard Satu Data OPD',
                'subtitle' => 'Sistem Informasi Pemerintahan Daerah - Satu Data Kabupaten',
                'scope' => $user?->opd?->nama ?? 'Seluruh OPD',
                'operator' => $user?->name ?? 'Administrator',
                'date' => $now->translatedFormat('l, d F Y'),
                'time' => $now->format('H:i:s'),
                'badge' => 'Satu Data OPD',
                'login_status' => 'Aktif',
            ],
            'kpis' => [
                $this->kpi('Total OPD', $opds->count(), 'Organisasi Perangkat Daerah aktif', 'primary'),
                $this->kpi('Total Dataset', $datasetStats->sum('dataset_count'), 'Dataset aktif yang tersedia', 'success'),
                $this->kpi('Data Terbit', $datasetStats->sum('published_count'), 'Data yang sudah dipublikasikan', 'warning'),
                $this->kpi('Kecamatan', $kecamatanCount, 'Jumlah kecamatan', 'info'),
            ],
            'opds' => $opds,
            'recent_datasets' => $recentDatasets,
            'quick_actions' => [
                $this->action('Input Data', 'Masukkan nilai data untuk periode berjalan.', route('filament.admin.resources.dataset-values.index'), 'primary'),
                $this->action('Katalog OPD', 'Lihat daftar OPD dan dataset mereka.', route('filament.admin.resources.opds.index'), 'success'),
                $this->action('Data OPD', 'Jelajahi data per OPD.', route('filament.admin.pages.data-opd'), 'warning'),
            ],
            'summary' => [
                'date' => $now->translatedFormat('d F Y'),
                'time' => $now->format('H:i:s'),
                'server_status' => 'Online',
                'database_status' => 'Connected',
                'api_status' => 'Operational',
            ],
        ];
    }

    private function kpi(string $label, int|string $value, string $description, string $color): array
    {
        return compact('label', 'value', 'description', 'color');
    }

    private function action(string $label, string $description, string $url, string $color): array
    {
        return compact('label', 'description', 'url', 'color');
    }
}
