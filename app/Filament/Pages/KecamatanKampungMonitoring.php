<?php

namespace App\Filament\Pages;

use App\Services\Dashboard\DashboardService;
use Filament\Pages\Page;

class KecamatanKampungMonitoring extends Page
{
    protected static ?string $slug = 'kecamatan-kampung';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Kecamatan & Kampung';
    protected static ?string $title = 'Kecamatan dan Kampung';
    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring Sangihe';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.kecamatan-kampung-monitoring';

    public static function shouldRegisterNavigation(): bool { return ExecutiveDashboard::canAccess(); }
    public static function canAccess(): bool { return ExecutiveDashboard::canAccess(); }

    protected function getViewData(): array
    {
        $service = app(DashboardService::class);
        return [
            'monitoring' => $service->getLeadershipMonitoring(),
            'officialInfographics' => $service->getOfficialInfographics(['3.12', '3.13', '3.26', '3.35', '3.36', '4.41', '4.47', '5.57', '5.59']),
            'pageTitle' => 'Kecamatan dan Kampung',
        ];
    }
}
