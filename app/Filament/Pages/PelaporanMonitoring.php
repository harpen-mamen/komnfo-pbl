<?php

namespace App\Filament\Pages;

use App\Services\Dashboard\DashboardService;
use Filament\Pages\Page;

class PelaporanMonitoring extends Page
{
    protected static ?string $slug = 'pelaporan-pimpinan';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Pelaporan';
    protected static ?string $title = 'Pelaporan';
    protected static string | \UnitEnum | null $navigationGroup = 'Monitoring Sangihe';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.pages.pelaporan-monitoring';

    public static function shouldRegisterNavigation(): bool { return ExecutiveDashboard::canAccess(); }
    public static function canAccess(): bool { return ExecutiveDashboard::canAccess(); }

    protected function getViewData(): array
    {
        return ['monitoring' => app(DashboardService::class)->getLeadershipMonitoring(), 'pageTitle' => 'Pelaporan'];
    }
}
