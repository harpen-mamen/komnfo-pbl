<?php

namespace App\Filament\Pages;

use App\Models\Opd;
use App\Services\Dashboard\DashboardService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard monitoring pimpinan daerah.
 */
class ExecutiveDashboard extends Page
{
    protected static ?string $slug = 'executive-dashboard';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Ringkasan Sangihe';
    protected static ?string $title = 'Keadaan Sangihe';
    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring Sangihe';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.executive-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user !== null && self::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user !== null && (
            $user->isWalidata()
            || $user->isSuperAdmin()
            || $user->isKepalaDaerah()
        );
    }

    public function getViewData(): array
    {
        $service = app(DashboardService::class);
        $officialInfographics = $service->getOfficialInfographics([
            '3.12', '3.13', '3.16', '3.17', '3.20', '3.21', '3.22', '3.29', '3.30',
            '3.35', '3.36', '4.37', '4.38', '4.41', '4.42', '4.43', '4.44', '4.46',
            '4.47', '5.57', '5.59', '5.61', '6.68', '6.71', '7.77', '7.78', '7.79',
            'IKU_DAERAH', 'PRODUK_DPRD', 'PULAU_KECIL_TERLUAR', '2.1', 'IRBI_2025', '8.80', '8.85', '8.86', '8.87',
        ]);
        $infographicsByOpd = collect($officialInfographics)
            ->groupBy(fn (array $item): int => (int) $item['dataset']->opd_id);
        $opds = Opd::query()
            ->where('aktif', true)
            ->with(['datasets' => fn ($query) => $query
                ->where('is_active', true)
                ->withCount(['values as official_published_values_count' => fn ($values) => $values
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))])])
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama']);
        $opdCards = $opds->map(function (Opd $opd) use ($infographicsByOpd): array {
            return [
                'id' => $opd->id,
                'code' => $opd->kode,
                'name' => $opd->nama,
                'active_dataset_count' => $opd->datasets->count(),
                'published_values_count' => (int) $opd->datasets->sum('official_published_values_count'),
                'infographics' => $infographicsByOpd->get((int) $opd->id, collect())->values(),
            ];
        });

        return [
            'monitoring' => $service->getLeadershipMonitoring(),
            'officialInfographics' => $officialInfographics,
            'opdCards' => $opdCards,
            'pageTitle' => 'Ringkasan Sangihe',
        ];
    }
}
