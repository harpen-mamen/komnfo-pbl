<?php

namespace App\Filament\Pages;

use App\Support\PanelAccess;
use Filament\Pages\Page;

class Laporan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-report';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan';
    protected static ?int $navigationSort = 9;
    protected static string $view = 'filament.pages.laporan';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel(), 403);
    }

    protected function getViewData(): array
    {
        $reports = [];

        if (PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel()) {
            $reports = array_merge($reports, [
                ['label' => 'Rumah PDF', 'url' => route('laporan.rumah.pdf')],
                ['label' => 'Rumah Excel', 'url' => route('laporan.rumah.excel')],
                ['label' => 'Keluarga PDF', 'url' => route('laporan.keluarga.pdf')],
                ['label' => 'Keluarga Excel', 'url' => route('laporan.keluarga.excel')],
                ['label' => 'Penduduk PDF', 'url' => route('laporan.penduduk.pdf')],
                ['label' => 'Penduduk Excel', 'url' => route('laporan.penduduk.excel')],
            ]);
        }

        if (PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel()) {
            $reports = array_merge($reports, [
                ['label' => 'Pendidikan Bulanan PDF', 'url' => route('laporan.pendidikan.pdf')],
                ['label' => 'Pendidikan Bulanan Excel', 'url' => route('laporan.pendidikan.excel')],
            ]);
        }

        if (PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel()) {
            $reports = array_merge($reports, [
                ['label' => 'Kesehatan Bulanan PDF', 'url' => route('laporan.kesehatan.pdf')],
                ['label' => 'Kesehatan Bulanan Excel', 'url' => route('laporan.kesehatan.excel')],
            ]);
        }

        return [
            'reports' => $reports,
        ];
    }
}
