<?php

namespace App\Filament\Pages;

use App\Models\Rumah;
use App\Support\PanelAccess;
use Filament\Pages\Page;

class PetaDesa extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Profil Kecamatan';
    protected static ?string $title = 'Profil Kecamatan';
    protected static ?int $navigationSort = 8;
    protected static string $view = 'filament.pages.peta-desa';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::isAdminKecamatan(), 403);
    }

    public function getMarkersProperty(): array
    {
        return Rumah::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->withCount(['keluarga'])
            ->with(['keluarga' => fn ($query) => $query->withCount('penduduk')])
            ->get()
            ->map(fn (Rumah $rumah) => [
                'kode_rumah' => $rumah->kode_rumah,
                'alamat' => $rumah->alamat_lengkap,
                'latitude' => (float) $rumah->latitude,
                'longitude' => (float) $rumah->longitude,
                'jumlah_kk' => $rumah->keluarga_count,
                'jumlah_penduduk' => $rumah->keluarga->sum('penduduk_count'),
                'status_rumah' => $rumah->status_rumah,
            ])
            ->values()
            ->all();
    }
}
