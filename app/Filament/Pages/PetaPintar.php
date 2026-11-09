<?php

namespace App\Filament\Pages;

use App\Services\DatasetSpatialDataService;
use App\Services\ThematicMapDataService;
use App\Support\PanelAccess;
use Filament\Pages\Page;

class PetaPintar extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Peta Pintar';
    protected static ?string $title = 'Peta Pintar';
    protected static ?int $navigationSort = 9;
    protected string $view = 'filament.pages.peta-pintar';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKominfoKabupaten();
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::isSuperAdmin() || PanelAccess::isKominfoKabupaten(), 403);
    }

    public function getTitle(): string
    {
        if (PanelAccess::isKominfoKabupaten()) {
            return 'Peta Pintar Kabupaten';
        } elseif (PanelAccess::isAdminDesa()) {
            return 'Peta Pintar Desa';
        } elseif (PanelAccess::isOperatorOpd()) {
            return 'Peta Pintar OPD';
        }
        return 'Peta Pintar Kecamatan';
    }

    public function getMarkersProperty(): array
    {
        // Modul rumah/keluarga lama telah diarsipkan; Peta Pintar saat ini memakai data sektoral.
        return [];
    }

    public function getDatasetSpatialFeaturesProperty(): array
    {
        return app(DatasetSpatialDataService::class)->mapFeatureCollectionForAdmin();
    }

    public function getThematicMapDataProperty(): array
    {
        return app(ThematicMapDataService::class)->forAdminMap();
    }

}
