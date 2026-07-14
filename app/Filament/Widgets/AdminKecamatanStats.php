<?php

namespace App\Filament\Widgets;

use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Support\PanelAccess;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

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
            Card::make('Jumlah Rumah', (clone $rumah)->count())->icon('heroicon-o-home')->color('primary'),
            Card::make('Jumlah KK', (clone $keluarga)->count())->icon('heroicon-o-user-group')->color('success'),
            Card::make('Jumlah Penduduk', (clone $penduduk)->count())->icon('heroicon-o-identification')->color('warning'),
            Card::make('Jumlah Laki-laki', (clone $penduduk)->where('jenis_kelamin', 'Laki-laki')->count()),
            Card::make('Jumlah Perempuan', (clone $penduduk)->where('jenis_kelamin', 'Perempuan')->count()),
            Card::make('Jumlah Balita', (clone $penduduk)->whereDate('tanggal_lahir', '>', $today->copy()->subYears(6))->count()),
            Card::make('Jumlah Anak', (clone $penduduk)->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(6))->whereDate('tanggal_lahir', '>', $today->copy()->subYears(18))->count()),
            Card::make('Jumlah Dewasa', (clone $penduduk)->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(18))->whereDate('tanggal_lahir', '>', $today->copy()->subYears(60))->count()),
            Card::make('Jumlah Lansia', (clone $penduduk)->whereDate('tanggal_lahir', '<=', $today->copy()->subYears(60))->count()),
        ];
    }
}
