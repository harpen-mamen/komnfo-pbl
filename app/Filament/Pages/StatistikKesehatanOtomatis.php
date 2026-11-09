<?php

namespace App\Filament\Pages;

use App\Services\HealthStatisticsService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class StatistikKesehatanOtomatis extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Statistik Kesehatan';
    protected static ?string $title = 'Statistik Kesehatan Otomatis';
    protected static ?string $pluralLabel = 'Statistik Kesehatan';
    protected static ?int $navigationSort = 17;

    protected string $view = 'filament.pages.statistik-kesehatan';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?int $kecamatanId = null;
    public int $tahun;
    public int $bulan;
    public array $statistik = [];

    public function mount(): void
    {
        $this->tahun = (int) now()->format('Y');
        $this->bulan = (int) now()->format('n');

        $user = Auth::user();
        if ($user) {
            $kecamatanId = $user->kecamatan_id ?? $user->desa?->kecamatan_id;
            if ($kecamatanId) {
                $this->kecamatanId = (int) $kecamatanId;
                $this->loadStatistics();
            }
        }
    }

    public function loadStatistics(): void
    {
        if (!$this->kecamatanId) {
            $this->statistik = [];
            return;
        }

        $service = app(HealthStatisticsService::class);
        $this->statistik = $service->calculateKecamatanStatistics(
            $this->kecamatanId,
            $this->tahun,
            $this->bulan
        );
    }

    public function updatedTahun(): void
    {
        $this->loadStatistics();
    }

    public function updatedBulan(): void
    {
        $this->loadStatistics();
    }

    public function updatedKecamatanId(): void
    {
        $this->loadStatistics();
    }
}
