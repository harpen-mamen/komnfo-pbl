<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Wilayah;
use App\Models\MobileFieldPoint;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;

class KecamatanProgressOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    protected function getCards(): array
    {
        $today = Carbon::today();

        $kecamatanId = PanelAccess::user()?->kecamatan_id ?: PanelAccess::user()?->desa?->parent_id;
        return [
            Stat::make('Titik Mobile Hari Ini', MobileFieldPoint::query()->whereDate('created_at', $today)->count())->icon('heroicon-o-map-pin')->color('primary'),
            Stat::make('Kiriman Hari Ini', SubmissionBatch::query()->where('kecamatan_id', $kecamatanId)->whereDate('created_at', $today)->where('module', SubmissionBatch::MODULE_LAPANGAN)->count())->icon('heroicon-o-arrow-up-tray')->color('success'),
            Stat::make('Desa Aktif', Wilayah::query()->where('parent_id', $kecamatanId)->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->count())->icon('heroicon-o-map')->color('primary'),
        ];
    }
}
