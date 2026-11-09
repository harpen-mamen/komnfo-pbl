<?php

namespace App\Filament\Pages;

use App\Support\PanelAccess;
use Filament\Pages\Page;

// Legacy report hub: current exports live alongside each dataset in the catalog.
class Laporan extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan';
    protected static ?int $navigationSort = 9;
    protected string $view = 'filament.pages.laporan';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin()
            || PanelAccess::isKependudukanDataOwner()
            || PanelAccess::canAccessKecamatanPanel()
            || PanelAccess::canAccessLapanganPanel()
            || PanelAccess::canAccessOpdPanel()
            || PanelAccess::canAccessOperatorPanel();
    }

    public function mount(): void
    {
        abort_unless(
            PanelAccess::isSuperAdmin()
                || PanelAccess::isKependudukanDataOwner()
                || PanelAccess::canAccessKecamatanPanel()
                || PanelAccess::canAccessLapanganPanel()
                || PanelAccess::canAccessOpdPanel()
                || PanelAccess::canAccessOperatorPanel(),
            403
        );
    }

    protected function getViewData(): array
    {
        $reports = [];

        return [
            'reports' => [],
            'notice' => 'Laporan rumah tangga lama telah dinonaktifkan. Gunakan katalog dataset dan ekspor dataset yang telah diterbitkan.',
        ];
    }
}
