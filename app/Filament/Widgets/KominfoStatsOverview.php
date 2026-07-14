<?php

namespace App\Filament\Widgets;

use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class KominfoStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    protected function getCards(): array
    {
        return [
            Card::make('Kabupaten', Kabupaten::query()->count())->icon('heroicon-o-office-building')->color('primary'),
            Card::make('Kecamatan', Kecamatan::query()->count())->icon('heroicon-o-location-marker')->color('success'),
            Card::make('Desa', Desa::query()->count())->icon('heroicon-o-map')->color('warning'),
            Card::make('Total Batch Disetujui', SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_APPROVED)->count())->icon('heroicon-o-check-circle'),
            Card::make('Total Rumah', Rumah::query()->inApprovedBatch()->count())->icon('heroicon-o-home'),
            Card::make('Total KK', Keluarga::query()->inApprovedBatch()->count())->icon('heroicon-o-user-group'),
            Card::make('Total Penduduk', Penduduk::query()->inApprovedBatch()->count())->icon('heroicon-o-identification'),
        ];
    }
}
