<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Rumah;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;

class ComprehensiveStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Statistik Komprehensif';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getCards(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $rumah = Rumah::query()->inApprovedBatch();
        $keluarga = Keluarga::query()->inApprovedBatch();
        $penduduk = Penduduk::query()->inApprovedBatch();

        $totalRumah = (clone $rumah)->count();
        $totalKeluarga = (clone $keluarga)->count();
        $totalPenduduk = (clone $penduduk)->count();

        $avgFamiliesPerHouse = $totalRumah > 0 ? round($totalKeluarga / $totalRumah, 2) : 0;
        $avgPeoplePerFamily = $totalKeluarga > 0 ? round($totalPenduduk / $totalKeluarga, 2) : 0;

        return [
            Stat::make('Total Rumah', $totalRumah)
                ->icon('heroicon-o-home')
                ->color('primary'),

            Stat::make('Total Keluarga', $totalKeluarga)
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Total Penduduk', $totalPenduduk)
                ->icon('heroicon-o-identification')
                ->color('warning'),

            Stat::make('Rata-rata KK per Rumah', $avgFamiliesPerHouse)
                ->icon('heroicon-o-chart-pie')
                ->color('info'),

            Stat::make('Rata-rata Anggota per KK', $avgPeoplePerFamily)
                ->icon('heroicon-o-users')
                ->color('gray'),

            Stat::make('Rasio Laki-laki : Perempuan', round(
                (clone $penduduk)->where('jenis_kelamin', 'Laki-laki')->count() /
                max(1, (clone $penduduk)->where('jenis_kelamin', 'Perempuan')->count()),
                2
            ))
                ->icon('heroicon-o-scale')
                ->color('danger'),
        ];
    }
}
