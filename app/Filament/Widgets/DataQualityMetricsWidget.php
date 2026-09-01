<?php

namespace App\Filament\Widgets;

use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\Keluarga;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class DataQualityMetricsWidget extends StatsOverviewWidget
{
    protected static ?string $heading = 'Metrik Kualitas Data';
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
            Card::make('Kelengkapan Data', $completenessPercentage . '%')
                ->icon('heroicon-o-check-circle')
                ->color($completenessPercentage >= 80 ? 'success' : ($completenessPercentage >= 60 ? 'warning' : 'danger')),
            
            Card::make('Data dengan Telepon', $withPhone)
                ->icon('heroicon-o-phone')
                ->color('info'),
            
            Card::make('Data dengan Pendidikan', $withEducation)
                ->icon('heroicon-o-academic-cap')
                ->color('success'),
            
            Card::make('Data dengan Pekerjaan', $withEmployment)
                ->icon('heroicon-o-briefcase')
                ->color('warning'),

            Card::make('Data dengan Penghasilan', $withIncome)
                ->icon('heroicon-o-currency-dollar')
                ->color('danger'),

            Card::make('Update Terakhir', Carbon::now()->diffForHumans())
                ->icon('heroicon-o-clock')
                ->color('secondary'),
        ];
    }
}
