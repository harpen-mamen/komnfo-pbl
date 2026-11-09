<?php

namespace App\Filament\Pages;

use App\Services\Dashboard\DashboardService;
use Filament\Pages\Page;

class RpjmdMonitoring extends Page
{
    protected static ?string $slug = 'rpjmd-monitoring';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Monitoring RPJMD';
    protected static ?string $title = 'Monitoring RPJMD';
    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring Sangihe';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.rpjmd-monitoring';

    public static function shouldRegisterNavigation(): bool { return ExecutiveDashboard::canAccess(); }
    public static function canAccess(): bool { return ExecutiveDashboard::canAccess(); }

    protected function getViewData(): array
    {
        return ['monitoring' => app(DashboardService::class)->getLeadershipMonitoring(), 'pageTitle' => 'Monitoring RPJMD'];
    }
}
