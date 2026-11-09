<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Penduduk;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Carbon;

class DataQualityMetricsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Metrik Kualitas Data';
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    protected function getCards(): array
    {
        // READ MODEL: Only approved data (Arsitektur §4.3)
        $penduduk = Penduduk::query()->visibleToCurrentUser()->inApprovedBatch();
        $totalPenduduk = (clone $penduduk)->count();

        // Calculate data completeness
        $withPhone = (clone $penduduk)->whereNotNull('nomor_hp')->where('nomor_hp', '!=', '')->count();
        $withEducation = (clone $penduduk)->whereNotNull('pendidikan')->where('pendidikan', '!=', '')->count();
        $withEmployment = (clone $penduduk)->whereNotNull('pekerjaan')->where('pekerjaan', '!=', '')->count();
        $withIncome = (clone $penduduk)->whereNotNull('penghasilan')->where('penghasilan', '>', 0)->count();

        $completenessPercentage = $totalPenduduk > 0
            ? round((($withPhone + $withEducation + $withEmployment + $withIncome) / ($totalPenduduk * 4)) * 100)
            : 0;

        return [
            Stat::make('Kelengkapan Data', $completenessPercentage . '%')
                ->icon('heroicon-o-check-circle')
                ->color($completenessPercentage >= 80 ? 'success' : ($completenessPercentage >= 60 ? 'warning' : 'danger')),

            Stat::make('Data dengan Telepon', $withPhone)
                ->icon('heroicon-o-phone')
                ->color('info'),

            Stat::make('Data dengan Pendidikan', $withEducation)
                ->icon('heroicon-o-academic-cap')
                ->color('success'),

            Stat::make('Data dengan Pekerjaan', $withEmployment)
                ->icon('heroicon-o-briefcase')
                ->color('warning'),

            Stat::make('Data dengan Penghasilan', $withIncome)
                ->icon('heroicon-o-currency-dollar')
                ->color('danger'),

            Stat::make('Update Terakhir', Carbon::now()->diffForHumans())
                ->icon('heroicon-o-clock')
                ->color('gray'),
        ];
    }
}
