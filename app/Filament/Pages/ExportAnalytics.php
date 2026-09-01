<?php

namespace App\Filament\Pages;

use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\Keluarga;
use App\Services\AnalyticsService;
use App\Support\PanelAccess;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class ExportAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-download';
    protected static ?string $navigationLabel = 'Export Data';
    protected static ?string $title = 'Export Analitik';
    protected static ?int $navigationSort = 10;
    protected static string $view = 'filament.pages.export-analytics';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel();
    }

    public function mount(): void
    {
        abort_unless(
            PanelAccess::isAdminKecamatan() || PanelAccess::canAccessKabupatenPanel(),
            403
        );
    }

    public function getAnalyticsSummaryProperty()
    {
        $penduduk = Penduduk::query()->visibleToCurrentUser();
        $rumah = Rumah::query()->visibleToCurrentUser();

        return AnalyticsService::getComprehensiveSummary($penduduk, $rumah);
    }

    public function exportPendudukCSV()
    {
        $pendudukQuery = Penduduk::query()
            ->visibleToCurrentUser()
            ->inApprovedBatch()
            ->with('keluarga.rumah.desa');

        $filename = 'penduduk_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($pendudukQuery) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'No', 'NIK', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 
                'Tanggal Lahir', 'Agama', 'Status Perkawinan', 'Pendidikan', 
                'Pekerjaan', 'Penghasilan', 'Status Penduduk', 'Desa'
            ]);

            // Data
            $index = 0;
            foreach ($pendudukQuery->cursor() as $p) {
                fputcsv($file, [
                    ++$index,
                    $p->nik,
                    $p->nama_lengkap,
                    $p->jenis_kelamin,
                    $p->tempat_lahir,
                    $p->tanggal_lahir?->format('Y-m-d'),
                    $p->agama,
                    $p->status_perkawinan,
                    $p->pendidikan,
                    $p->pekerjaan,
                    $p->penghasilan,
                    $p->status_penduduk,
                    $p->keluarga?->rumah?->desa?->nama,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportStatisticsJSON()
    {
        $analytics = $this->analyticsSummary;
        $filename = 'statistik_' . Carbon::now()->format('Y-m-d_His') . '.json';

        return response()->json($analytics)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
