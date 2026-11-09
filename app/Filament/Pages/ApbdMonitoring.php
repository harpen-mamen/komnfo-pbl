<?php

namespace App\Filament\Pages;

use App\Services\Dashboard\DashboardService;
use Filament\Pages\Page;

class ApbdMonitoring extends Page
{
    protected static ?string $slug = 'apbd-monitoring';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Monitoring APBD';
    protected static ?string $title = 'Monitoring APBD';
    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring Sangihe';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.apbd-monitoring';

    public static function shouldRegisterNavigation(): bool { return ExecutiveDashboard::canAccess(); }
    public static function canAccess(): bool { return ExecutiveDashboard::canAccess(); }

    protected function getViewData(): array
    {
        return ['monitoring' => app(DashboardService::class)->getLeadershipMonitoring(), 'pageTitle' => 'Monitoring APBD'];
    }
}
