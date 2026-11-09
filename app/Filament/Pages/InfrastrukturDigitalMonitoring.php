<?php

namespace App\Filament\Pages;

use App\Services\Dashboard\DashboardService;
use Filament\Pages\Page;

class InfrastrukturDigitalMonitoring extends Page
{
    protected static ?string $slug = 'infrastruktur-digital';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'Infrastruktur Digital';
    protected static ?string $title = 'Infrastruktur Digital';
    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring Sangihe';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.infrastruktur-digital-monitoring';

    public static function shouldRegisterNavigation(): bool { return ExecutiveDashboard::canAccess(); }
    public static function canAccess(): bool { return ExecutiveDashboard::canAccess(); }

    protected function getViewData(): array
    {
        return ['monitoring' => app(DashboardService::class)->getLeadershipMonitoring(), 'pageTitle' => 'Infrastruktur Digital'];
    }
}
