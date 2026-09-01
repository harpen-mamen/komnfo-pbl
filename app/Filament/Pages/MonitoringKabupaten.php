<?php

namespace App\Filament\Pages;

use App\Models\Kecamatan;
use App\Support\PanelAccess;
use Filament\Pages\Page;

class MonitoringKabupaten extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Monitoring Kabupaten';
    protected static ?string $title = 'Monitoring Kabupaten';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.monitoring-kabupaten';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan()
            || PanelAccess::isKominfoKabupaten()
            || PanelAccess::isOperatorOpd();
    }

    public function mount(): void
    {
        abort_unless(
            PanelAccess::isAdminKecamatan()
            || PanelAccess::isKominfoKabupaten()
            || PanelAccess::isOperatorOpd(),
            403
        );
    }

    public function getTitle(): string
    {
        if (PanelAccess::isKominfoKabupaten()) {
            return 'Monitoring Kabupaten';
        } elseif (PanelAccess::isAdminDesa()) {
            return 'Monitoring Desa';
        } elseif (PanelAccess::isOperatorOpd()) {
            return 'Monitoring OPD';
        }
        return 'Monitoring Kecamatan';
    }

    public function getRowsProperty()
    {
        return Kecamatan::query()
            ->withCount(['desas'])
            ->with(['desas.rumah.keluarga.penduduk'])
            ->orderBy('nama')
            ->get()
            ->map(function (Kecamatan $kecamatan): array {
                $rumah = $kecamatan->desas->flatMap->rumah;
                $keluarga = $rumah->flatMap->keluarga;
                $penduduk = $keluarga->flatMap->penduduk;

                return [
                    'nama' => $kecamatan->nama,
                    'desa' => $kecamatan->desas_count,
                    'rumah' => $rumah->count(),
                    'keluarga' => $keluarga->count(),
                    'penduduk' => $penduduk->count(),
                ];
            });
    }
}
