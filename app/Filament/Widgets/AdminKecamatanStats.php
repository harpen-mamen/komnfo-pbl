<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;

class AdminKecamatanStats extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    protected function getCards(): array
    {
        $rumah = Rumah::query()->visibleToCurrentUser()->inApprovedBatch();
        $keluarga = Keluarga::query()->visibleToCurrentUser()->inApprovedBatch();
        $penduduk = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch();
        $today = Carbon::today();

        return [
            Stat::make('Jumlah Rumah', (clone $rumah)->count())->icon('heroicon-o-home')->color('primary'),
            Stat::make('Jumlah KK', (clone $keluarga)->count())->icon('heroicon-o-user-group')->color('success'),
            Stat::make('Jumlah Penduduk', (clone $penduduk)->count())->icon('heroicon-o-identification')->color('warning'),
            Stat::make('Jumlah Laki-laki', (clone $penduduk)->where('jenis_kelamin', 'Laki-laki')->count()),
            Stat::make('Jumlah Perempuan', (clone $penduduk)->where('jenis_kelamin', 'Perempuan')->count()),
            Stat::make('Jumlah Balita', (clone $penduduk)->whereDate('tanggal_lahir', '>', $today->copy()->subYears(6))->count()),
            Stat::make('Jumlah Anak', (clone $penduduk)->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(6))->whereDate('tanggal_lahir', '>', $today->copy()->subYears(18))->count()),
            Stat::make('Jumlah Dewasa', (clone $penduduk)->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(18))->whereDate('tanggal_lahir', '>', $today->copy()->subYears(60))->count()),
            Stat::make('Jumlah Lansia', (clone $penduduk)->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(60))->count()),
        ];
    }
}
