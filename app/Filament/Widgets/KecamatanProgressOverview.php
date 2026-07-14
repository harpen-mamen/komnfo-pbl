<?php

namespace App\Filament\Widgets;

use App\Models\Desa;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class KecamatanProgressOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    protected function getCards(): array
    {
        $today = Carbon::today();

        return [
            Card::make('Penduduk Kecamatan', Penduduk::query()->visibleToCurrentUser()->inApprovedBatch()->count())->icon('heroicon-o-identification')->color('primary'),
            Card::make('KK Kecamatan', Keluarga::query()->visibleToCurrentUser()->inApprovedBatch()->count())->icon('heroicon-o-user-group')->color('success'),
            Card::make('Rumah Kecamatan', Rumah::query()->visibleToCurrentUser()->inApprovedBatch()->count())->icon('heroicon-o-home')->color('warning'),
            Card::make('Data Hari Ini', Penduduk::query()->visibleToCurrentUser()->whereDate('created_at', $today)->count())->icon('heroicon-o-calendar')->color('gray'),
            Card::make('Import Hari Ini', \App\Models\ImportPendudukLog::query()->visibleToCurrentUser()->whereDate('created_at', $today)->count())->icon('heroicon-o-upload')->color('gray'),
            Card::make('Desa Aktif', Desa::query()->where('kecamatan_id', PanelAccess::user()?->kecamatan_id)->count())->icon('heroicon-o-map')->color('primary'),
        ];
    }
}
