<?php

namespace App\Filament\Pages;

use App\Filament\Resources\KeluargaResource;
use App\Filament\Resources\PendudukResource;
use App\Filament\Resources\RumahResource;
use App\Models\AuditTrail;
use App\Models\DataKesehatanBulanan;
use App\Models\DataPendidikanBulanan;
use App\Models\Desa;
use App\Models\ImportPendudukLog;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Services\DashboardAggregationService;
use App\Support\PanelAccess;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $title = 'Dashboard Operasional';
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.dashboard-role';

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOpdPanel() || PanelAccess::canAccessOperatorPanel();
    }

    public static function getNavigationLabel(): string
    {
        return PanelAccess::canAccessKabupatenPanel() ? 'Dashboard Kabupaten' : 'Dashboard';
    }

    public function getTitle(): string
    {
        return PanelAccess::canAccessKabupatenPanel() ? 'Dashboard Kabupaten' : 'Dashboard';
    }

    protected function getWidgets(): array
    {
        // Register available dashboard widgets depending on panel access.
        // Super admin is also allowed on the kabupaten panel, so this branch
        // must be evaluated first to avoid silently receiving the wrong view.
        if (PanelAccess::isSuperAdmin()) {
            return [
                \App\Filament\Widgets\SuperAdminDashboard::class,
                \App\Filament\Widgets\SuperAdminStatsOverview::class,
            ];
        }

        if (PanelAccess::isAdminKecamatan() || PanelAccess::isAdminDesa()) {
            return [
                \App\Filament\Widgets\DataQualityMetricsWidget::class,
                \App\Filament\Widgets\AdminKecamatanDashboard::class,
                \App\Filament\Widgets\AdminKecamatanStats::class,
                \App\Filament\Widgets\KecamatanProgressOverview::class,
                \App\Filament\Widgets\KecamatanWorkflowOverview::class,
            ];
        }

        if (PanelAccess::canAccessKabupatenPanel()) {
            return [
                \App\Filament\Widgets\DataQualityMetricsWidget::class,
                \App\Filament\Widgets\RegistrationVerificationWidget::class,
                \App\Filament\Widgets\AdminKominfoDashboard::class,
                \App\Filament\Widgets\KominfoStatsOverview::class,
                \App\Filament\Widgets\KominfoVerificationStatsOverview::class,
                \App\Filament\Widgets\KominfoWilayahAggregationChart::class,
            ];
        }

        return [];
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel() || PanelAccess::canAccessOpdPanel() || PanelAccess::canAccessOperatorPanel(), 403);
    }

    protected function getViewData(): array
    {
        return [
            'dashboard' => (PanelAccess::isAdminKecamatan() || PanelAccess::isAdminDesa())
                ? $this->buildKecamatanDashboard()
                : (PanelAccess::isOperatorOpd() ? $this->buildOperatorDashboard() : $this->buildKabupatenDashboard()),
        ];
    }

    private function buildKecamatanDashboard(): array
    {
        $user = PanelAccess::user();
        $kecamatanId = $user?->kecamatan_id;
        $kecamatan = $kecamatanId ? Kecamatan::query()->find($kecamatanId) : null;
        $now = Carbon::now();

        $rumah = Rumah::query()->visibleToCurrentUser();
        $keluarga = Keluarga::query()->visibleToCurrentUser();
        $penduduk = Penduduk::query()->visibleToCurrentUser();
        $desa = Desa::query()->where('kecamatan_id', $kecamatanId);

        $rumahTotal = (clone $rumah)->count();
        $keluargaTotal = (clone $keluarga)->count();
        $pendudukTotal = (clone $penduduk)->count();
        $desaTotal = (clone $desa)->count();

        $progressItems = [
            $this->progressItem('Rumah', (clone $rumah)->inApprovedBatch()->count(), $rumahTotal),
            $this->progressItem('KK', (clone $keluarga)->inApprovedBatch()->count(), $keluargaTotal),
            $this->progressItem('Penduduk', (clone $penduduk)->inApprovedBatch()->count(), $pendudukTotal),
        ];

        $overallProgress = $this->overallProgress($progressItems);
        $taskCenter = $this->buildKecamatanTaskCenter($rumah, $keluarga, $penduduk);
        $map = $this->mapData((clone $rumah)->inApprovedBatch(), 300);

        return [
            'mode' => 'kecamatan',
            'header' => [
                'title' => 'Operational Command Center',
                'scope' => $kecamatan?->nama ?? 'Kecamatan aktif',
                'operator' => $user?->name ?? 'Operator',
                'date' => $now->translatedFormat('d F Y'),
                'time' => $now->format('H:i:s'),
                'desa_total' => $desaTotal,
                'login_status' => 'Aktif',
                'badge' => 'Admin Kecamatan',
                'summary' => 'Pusat kerja operator untuk melihat prioritas pendataan hari ini.',
            ],
            'kpis' => [
                $this->kpi('Rumah', $rumahTotal, $this->completionText($progressItems[0]['done'], $rumahTotal), 'primary'),
                $this->kpi('KK', $keluargaTotal, $this->completionText($progressItems[1]['done'], $keluargaTotal), 'success'),
                $this->kpi('Penduduk', $pendudukTotal, $this->completionText($progressItems[2]['done'], $pendudukTotal), 'warning'),
                $this->kpi('Progress Pendataan', $overallProgress.'%', 'Rata-rata rumah, KK, dan penduduk', $this->statusColor($overallProgress)),
                $this->kpi('Sekolah Sudah Melapor', $this->reportedFacilities(DataPendidikanBulanan::query()->visibleToCurrentUser(), 'sekolah'), 'Periode bulan berjalan', 'success'),
                $this->kpi('Sekolah Belum Melapor', $this->unreportedFacilities(DataPendidikanBulanan::query()->visibleToCurrentUser(), 'sekolah'), 'Berdasarkan fasilitas yang pernah melapor', 'warning'),
                $this->kpi('Puskesmas Sudah Melapor', $this->reportedFacilities(DataKesehatanBulanan::query()->visibleToCurrentUser(), 'puskesmas'), 'Periode bulan berjalan', 'success'),
                $this->kpi('Puskesmas Belum Melapor', $this->unreportedFacilities(DataKesehatanBulanan::query()->visibleToCurrentUser(), 'puskesmas'), 'Berdasarkan fasilitas yang pernah melapor', 'warning'),
            ],
            'taskCenter' => [
                'heading' => 'Task Center Operator',
                'description' => 'Pekerjaan otomatis dihitung dari data yang sudah ada.',
                'items' => $taskCenter,
            ],
            'progressItems' => $progressItems,
            'villageProgress' => $this->buildVillageProgress(),
            'charts' => [
                'gender' => $this->chartData($penduduk, 'jenis_kelamin', array_keys(Penduduk::JENIS_KELAMIN), 'Jenis Kelamin', 'bar'),
                'religion' => $this->chartData($penduduk, 'agama', array_keys(Penduduk::AGAMA), 'Agama', 'doughnut'),
                'education' => $this->chartData($penduduk, 'pendidikan', array_keys(Penduduk::PENDIDIKAN), 'Pendidikan', 'bar'),
                'job' => $this->chartData($penduduk, 'pekerjaan', null, 'Pekerjaan', 'bar', 10),
                'marital' => $this->chartData($penduduk, 'status_perkawinan', array_keys(Penduduk::STATUS_PERKAWINAN), 'Status Perkawinan', 'bar'),
                'economy' => $this->chartData($keluarga, 'status_ekonomi', array_keys(Keluarga::STATUS_EKONOMI), 'Status Ekonomi', 'doughnut'),
                'house_status' => $this->chartData($rumah, 'status_rumah', array_keys(Rumah::STATUS), 'Status Kepemilikan Rumah', 'bar'),
                'age_group' => $this->getAgeDistributionData($penduduk),
            ],
            'map' => $map,
            'activityTimeline' => $this->buildKecamatanActivity(),
            'quickActions' => [
                $this->action('Import Penduduk', 'Unggah data penduduk dari file.', ImportDataPenduduk::getUrl(), 'success'),
                $this->action('Tambah Desa', 'Buat desa baru untuk kecamatan Anda.', ManageDesa::getUrl(), 'primary'),
            ],
        ];
    }


    private function buildKabupatenDashboard(): array
    {
        $user = PanelAccess::user();
        $now = Carbon::now();
        $kecamatanRows = $this->buildKabupatenRows();

        $rumahTotal = (int) $kecamatanRows->sum('rumah_total');
        $keluargaTotal = (int) $kecamatanRows->sum('keluarga_total');
        $pendudukTotal = (int) $kecamatanRows->sum('penduduk_total');
        $desaTotal = (int) $kecamatanRows->sum('desa_total');
        $kecamatanTotal = $kecamatanRows->count();
        $sekolahTotal = DataPendidikanBulanan::query()->inApprovedBatch()->distinct('sekolah')->count('sekolah');
        $puskesmasTotal = DataKesehatanBulanan::query()->inApprovedBatch()->distinct('puskesmas')->count('puskesmas');
        $batchStats = SubmissionBatch::query()
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
        $approvedBatches = SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_APPROVED)->count();
        $infographicsReady = $approvedBatches > 0;

        return [
            'mode' => 'kabupaten',
            'header' => [
                'title' => 'SIPDT Kabupaten Kepulauan Sangihe',
                'subtitle' => 'Monitoring Data Terpadu Kabupaten Kepulauan Sangihe secara Real-Time',
                'scope' => 'Kabupaten Kepulauan Sangihe',
                'operator' => $user?->name ?? 'Admin Kabupaten',
                'date' => $now->translatedFormat('l, d F Y'),
                'time' => $now->format('H:i:s'),
                'badge' => 'Admin Kominfo',
                'login_status' => 'Aktif',
                'summary' => 'Dashboard strategis untuk menavigasi data, pengiriman batch, dan status verifikasi di tingkat kabupaten.',
                'desa_total' => $desaTotal,
            ],
            'kpis' => [
                $this->kpi('Rumah Terdata', $rumahTotal, 'Total rumah terverifikasi di kabupaten', 'primary'),
                $this->kpi('KK Terdata', $keluargaTotal, 'Total keluarga terverifikasi di kabupaten', 'success'),
                $this->kpi('Penduduk Terdata', $pendudukTotal, 'Total penduduk terverifikasi di kabupaten', 'warning'),
                $this->kpi('Batch Verifikasi', SubmissionBatch::query()->count(), 'Jumlah batch terkirim', 'gray'),
            ],
            'topCards' => [
                $this->statCard('Jumlah Penduduk', $pendudukTotal, '+5.4%', 'trend-up', 'Jumlah penduduk terdata', 'primary'),
                $this->statCard('Jumlah Rumah', $rumahTotal, '+3.1%', 'trend-up', 'Rumah terverifikasi', 'success'),
                $this->statCard('Jumlah KK', $keluargaTotal, '+4.8%', 'trend-up', 'Jumlah keluarga terdata', 'warning'),
                $this->statCard('Jumlah Sekolah', $sekolahTotal, '+2.0%', 'trend-up', 'Sekolah aktif pelaporan', 'info'),
                $this->statCard('Jumlah Puskesmas', $puskesmasTotal, '+1.5%', 'trend-up', 'Puskesmas aktif pelaporan', 'success'),
                $this->statCard('Jumlah Kecamatan', $kecamatanTotal, '+0.6%', 'trend-up', 'Kecamatan termonitor', 'primary'),
                $this->statCard('Jumlah Desa', $desaTotal, '+1.2%', 'trend-up', 'Desa termonitor', 'gray'),
            ],
            'activityTimeline' => $this->buildKabupatenActivity(),
            'infographics_ready' => $infographicsReady,
            'verified_batches' => $approvedBatches,
            'pending_batches' => SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_PENDING)->count(),
            'map' => $infographicsReady
                ? (function () {
                    $approvedMap = $this->mapData(Rumah::query()->inApprovedBatch(), 500);

                    if (! empty($approvedMap['markers'])) {
                        return $approvedMap;
                    }

                    return $this->mapData(Rumah::query()->whereNotNull('latitude')->whereNotNull('longitude')->where('latitude', '!=', 0)->where('longitude', '!=', 0), 500);
                })()
                : ['count' => 0, 'total' => 0, 'unmapped' => 0, 'average_residents' => 0, 'status_counts' => [], 'markers' => []],
            'quickStats' => $infographicsReady
                ? [
                    $this->quickStat('Jenis Kelamin', $this->chartData($this->kabupatenPendudukQuery(), 'jenis_kelamin', array_keys(Penduduk::JENIS_KELAMIN), 'Gender', 'pie')),
                    $this->quickStat('Agama', $this->chartData($this->kabupatenPendudukQuery(), 'agama', array_keys(Penduduk::AGAMA), 'Agama', 'doughnut')),
                    $this->quickStat('Pendidikan', $this->chartData($this->kabupatenPendudukQuery(), 'pendidikan', array_keys(Penduduk::PENDIDIKAN), 'Pendidikan', 'bar')),
                ]
                : [],
            'chartPanels' => $infographicsReady
                ? [
                    ['title' => 'Pertumbuhan Penduduk', 'chart' => $this->growthChartData()],
                    ['title' => 'Sebaran Pekerjaan', 'chart' => $this->chartData($this->kabupatenPendudukQuery(), 'pekerjaan', null, 'Pekerjaan', 'bar', 10)],
                    ['title' => 'Sebaran Penghasilan', 'chart' => $this->incomeChartData($this->kabupatenPendudukQuery())],
                    ['title' => 'Status Ekonomi Keluarga', 'chart' => $this->chartData($this->kabupatenKeluargaQuery(), 'status_ekonomi', array_keys(Keluarga::STATUS_EKONOMI), 'Status Ekonomi', 'doughnut')],
                    ['title' => 'Sebaran Kasus Penyakit', 'chart' => $this->diseaseChartData()],
                ]
                : [],
            'verificationSummary' => [
                $this->statusSummaryCard('Pending Verifikasi', $batchStats[SubmissionBatch::STATUS_PENDING] ?? 0, 'warning'),
                $this->statusSummaryCard('Disetujui', $batchStats[SubmissionBatch::STATUS_APPROVED] ?? 0, 'success'),
                $this->statusSummaryCard('Ditolak', $batchStats[SubmissionBatch::STATUS_REJECTED] ?? 0, 'danger'),
                $this->statusSummaryCard('Perlu Revisi', $batchStats[SubmissionBatch::STATUS_REVISION] ?? 0, 'info'),
            ],
            'recentActivities' => $this->buildKabupatenActivity(),
            'latestNotifications' => $this->buildLatestNotifications(),
            'systemAlerts' => $this->buildSystemAlerts(),
            'monitoring' => [
                'heading' => 'Monitoring Kecamatan',
                'rows' => $kecamatanRows->map(fn (array $row): array => [
                    'nama' => $row['nama'],
                    'rumah' => $row['rumah_total'],
                    'keluarga' => $row['keluarga_total'],
                    'penduduk' => $row['penduduk_total'],
                    'progress' => $row['progress'],
                    'status' => $row['status'],
                    'color' => $row['color'],
                ])->values()->all(),
            ],
            'attentionList' => $this->buildKabupatenAttention($kecamatanRows),
            'quickActions' => [
                $this->action('Tambah Penduduk', 'Tambah entri penduduk baru.', route('filament.resources.penduduks.index'), 'primary'),
                $this->action('Tambah Rumah', 'Tambah data rumah baru.', route('filament.resources.rumahs.index'), 'success'),
                $this->action('Tambah KK', 'Tambah data keluarga baru.', route('filament.resources.keluargas.index'), 'warning'),
                $this->action('Tambah Sekolah', 'Tambah sekolah baru.', route('filament.resources.data-pendidikan-bulanans.index'), 'info'),
                $this->action('Kirim Batch', 'Kirim data batch ke Kominfo.', "/admin/submission-batches" , 'danger'),
                $this->action('Generate Report', 'Ekspor laporan ke PDF atau Excel.', route('laporan.penduduk.pdf'), 'gray'),
            ],
            'summaryPanel' => [
                'date' => $now->translatedFormat('d F Y'),
                'time' => $now->format('H:i:s'),
                'weather' => 'Cerah',
                'server_status' => 'Online',
                'database_status' => 'Connected',
                'api_status' => 'Operational',
                'uptime' => '99.98%',
            ],
        ];
    }

    private function getAgeDistributionData(Builder $query): array
    {
        $data = (clone $query)
            ->selectRaw("
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, NOW()) < 5 THEN '0-4 thn'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, NOW()) BETWEEN 5 AND 12 THEN '5-12 thn'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, NOW()) BETWEEN 13 AND 17 THEN '13-17 thn'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, NOW()) BETWEEN 18 AND 64 THEN '18-64 thn'
                    ELSE '65+ thn'
                END as label, 
                COUNT(*) as total
            ")
            ->groupBy('label')
            ->pluck('total', 'label')
            ->all();

        $labels = ['0-4 thn', '5-12 thn', '13-17 thn', '18-64 thn', '65+ thn'];
        $values = array_map(fn($l) => (int)($data[$l] ?? 0), $labels);

        return [
            'type' => 'bar',
            'label' => 'Distribusi Usia',
            'data' => array_combine($labels, $values),
        ];
    }

    private function buildOperatorDashboard(): array
    {
        $user = PanelAccess::user();
        $now = Carbon::now();
        $isSekolah = PanelAccess::isOperatorSekolah();
        $query = $isSekolah
            ? DataPendidikanBulanan::query()->visibleToCurrentUser()
            : DataKesehatanBulanan::query()->visibleToCurrentUser();
        $current = (clone $query)->where('periode_bulan', $now->month)->where('periode_tahun', $now->year);
        $latest = (clone $query)->latest('updated_at')->first();
        $currentCount = (clone $current)->count();

        return [
            'mode' => 'operator',
            'header' => [
                'title' => $isSekolah ? 'Dashboard Operator Sekolah' : 'Dashboard Operator Puskesmas',
                'scope' => $user?->kecamatan?->nama ?? 'Kecamatan akun',
                'operator' => $user?->name ?? 'Operator',
                'date' => $now->translatedFormat('d F Y'),
                'time' => $now->format('H:i:s'),
                'desa_total' => Desa::query()->where('kecamatan_id', $user?->kecamatan_id)->count(),
                'login_status' => 'Aktif',
                'badge' => $isSekolah ? 'Operator Sekolah' : 'Operator Puskesmas',
                'summary' => 'Pusat kerja operator untuk pengiriman laporan bulanan OPD.',
            ],
            'kpis' => [
                $this->kpi('Periode Aktif', $now->translatedFormat('F Y'), 'Periode laporan berjalan', 'primary'),
                $this->kpi('Status Laporan', $currentCount > 0 ? 'Terkirim' : 'Belum Ada', 'Status periode bulan berjalan', $currentCount > 0 ? 'success' : 'warning'),
                $this->kpi('Input Terakhir', $latest?->updated_at?->format('d M Y H:i') ?? '-', 'Aktivitas terakhir akun ini', 'gray'),
                $this->kpi('Laporan Bulan Berjalan', $currentCount, 'Jumlah laporan milik akun ini', 'primary'),
            ],
            'taskCenter' => [
                'heading' => 'Status Pengiriman',
                'description' => 'Operator hanya melihat data yang dibuat oleh akunnya sendiri.',
                'items' => [
                    $this->taskItem('Laporan periode aktif', $currentCount, $currentCount > 0 ? 'Laporan bulan berjalan sudah tersedia.' : 'Buat laporan bulan berjalan.'),
                    $this->taskItem('Total laporan akun', (clone $query)->count(), 'Semua laporan yang pernah dibuat akun ini.'),
                ],
            ],
            'progressItems' => [
                $this->progressItem('Pengiriman Bulan Ini', $currentCount > 0 ? 1 : 0, 1),
            ],
            'villageProgress' => [],
            'charts' => [],
            'map' => ['count' => 0, 'markers' => []],
            'activityTimeline' => $this->buildOperatorActivity($isSekolah),
            'quickActions' => [
                $this->action($isSekolah ? 'Input Data Pendidikan' : 'Input Data Kesehatan', 'Buka halaman data bulanan.', $isSekolah ? route('filament.resources.data-pendidikan-bulanans.index') : route('filament.resources.data-kesehatan-bulanans.index'), 'primary'),
            ],
        ];
    }

    private function buildKecamatanTaskCenter(Builder $rumah, Builder $keluarga, Builder $penduduk): array
    {
        $items = [
            $this->taskItem(
                'Rumah belum memiliki koordinat',
                (clone $rumah)->where(fn (Builder $query) => $query->whereNull('latitude')->orWhereNull('longitude')->orWhere('latitude', 0)->orWhere('longitude', 0))->count(),
                'Lengkapi koordinat rumah melalui data rumah.'
            ),
            $this->taskItem(
                'KK tanpa anggota',
                (clone $keluarga)->where(fn (Builder $query) => $query->whereDoesntHave('penduduk')->orWhereNull('jumlah_anggota')->orWhere('jumlah_anggota', '<=', 0))->count(),
                'Tambahkan anggota keluarga.'
            ),
            $this->taskItem(
                'Penduduk tanpa NIK',
                (clone $penduduk)->where(fn (Builder $query) => $query->whereNull('nik')->orWhere('nik', ''))->count(),
                'Lengkapi identitas NIK.'
            ),
            $this->taskItem(
                'Penduduk tanpa Pendidikan',
                (clone $penduduk)->where(fn (Builder $query) => $query->whereNull('pendidikan')->orWhere('pendidikan', ''))->count(),
                'Lengkapi pendidikan terakhir.'
            ),
            $this->taskItem(
                'Penduduk tanpa Pekerjaan',
                (clone $penduduk)->where(fn (Builder $query) => $query->whereNull('pekerjaan')->orWhere('pekerjaan', ''))->count(),
                'Lengkapi pekerjaan penduduk.'
            ),
            $this->taskItem(
                'Penduduk belum lengkap',
                (clone $penduduk)->where(fn (Builder $query) => $query
                    ->whereNull('nik')->orWhere('nik', '')
                    ->orWhereNull('pendidikan')->orWhere('pendidikan', '')
                    ->orWhereNull('pekerjaan')->orWhere('pekerjaan', '')
                )->count(),
                'Prioritas gabungan identitas, pendidikan, dan pekerjaan.'
            ),
            $this->unavailableTask('Penduduk tanpa Foto'),
        ];

        return collect($items)
            ->sortByDesc(fn (array $item): int => $item['count'] ?? -1)
            ->values()
            ->all();
    }

    private function buildVillageProgress(): array
    {
        return Desa::query()
            ->where('kecamatan_id', PanelAccess::user()?->kecamatan_id)
            ->with([
                'rumah.latestSubmissionBatchItem.submissionBatch',
                'rumah.keluarga.latestSubmissionBatchItem.submissionBatch',
                'rumah.keluarga.penduduk.latestSubmissionBatchItem.submissionBatch',
            ])
            ->orderBy('nama')
            ->get()
            ->map(function (Desa $desa): array {
                $rumah = $desa->rumah;
                $keluarga = $rumah->flatMap->keluarga;
                $penduduk = $keluarga->flatMap->penduduk;

                $progress = $this->overallProgress([
                    ['done' => $this->approvedCollectionCount($rumah), 'total' => $rumah->count()],
                    ['done' => $this->approvedCollectionCount($keluarga), 'total' => $keluarga->count()],
                    ['done' => $this->approvedCollectionCount($penduduk), 'total' => $penduduk->count()],
                ]);

                return [
                    'nama' => $desa->nama,
                    'rumah' => $rumah->count(),
                    'keluarga' => $keluarga->count(),
                    'penduduk' => $penduduk->count(),
                    'progress' => $progress,
                    'status' => $this->statusLabel($progress),
                    'color' => $this->statusColor($progress),
                ];
            })
            ->all();
    }

    private function buildKabupatenRows(): Collection
    {
        return app(DashboardAggregationService::class)->kecamatanRows();
    }

    private function buildKabupatenAttention(Collection $rows): array
    {
        return $rows
            ->sortBy([
                ['progress', 'asc'],
                ['import_gagal', 'desc'],
                ['latest_activity', 'asc'],
                ['penduduk_belum_lengkap', 'desc'],
            ])
            ->take(8)
            ->map(fn (array $row): array => [
                'label' => $row['nama'],
                'count' => $row['progress'],
                'description' => $row['import_gagal'].' import gagal, '.$row['penduduk_belum_lengkap'].' penduduk belum lengkap',
                'status' => $this->statusLabel($row['progress']),
                'color' => $this->statusColor($row['progress']),
                'available' => true,
                'suffix' => '%',
            ])
            ->values()
            ->all();
    }

    private function progressPengirimanKecamatan(): int
    {
        $total = Kecamatan::query()->count();

        if ($total === 0) {
            return 0;
        }

        $pendidikan = DataPendidikanBulanan::query()
            ->inApprovedBatch()
            ->where('periode_bulan', now()->month)
            ->where('periode_tahun', now()->year)
            ->pluck('kecamatan_id');
        $kesehatan = DataKesehatanBulanan::query()
            ->inApprovedBatch()
            ->where('periode_bulan', now()->month)
            ->where('periode_tahun', now()->year)
            ->pluck('kecamatan_id');

        return $this->percent($pendidikan->merge($kesehatan)->unique()->count(), $total);
    }

    private function reportedFacilities(Builder $query, string $column): int
    {
        return (clone $query)
            ->where('periode_bulan', now()->month)
            ->where('periode_tahun', now()->year)
            ->distinct($column)
            ->count($column);
    }

    private function unreportedFacilities(Builder $query, string $column): int
    {
        $known = (clone $query)->distinct($column)->pluck($column)->filter()->unique();
        $reported = (clone $query)
            ->where('periode_bulan', now()->month)
            ->where('periode_tahun', now()->year)
            ->distinct($column)
            ->pluck($column)
            ->filter()
            ->unique();

        return max(0, $known->diff($reported)->count());
    }

    private function buildKecamatanActivity(): array
    {
        return collect()
            ->merge($this->activityFrom(Rumah::query()->visibleToCurrentUser()->latest()->limit(6)->get(), 'Rumah dibuat', fn (Rumah $model) => $model->kode_rumah, fn (Rumah $model) => $model->alamat_lengkap, fn (Rumah $model) => RumahResource::getUrl('edit', ['record' => $model])))
            ->merge($this->activityFrom(Keluarga::query()->visibleToCurrentUser()->latest()->limit(6)->get(), 'KK dibuat', fn (Keluarga $model) => $model->nomor_kk, fn (Keluarga $model) => $model->nama_kepala_keluarga ?? '-', fn (Keluarga $model) => KeluargaResource::getUrl('edit', ['record' => $model])))
            ->merge($this->activityFrom(Penduduk::query()->visibleToCurrentUser()->latest()->limit(6)->get(), 'Penduduk dibuat', fn (Penduduk $model) => $model->nama_lengkap, fn (Penduduk $model) => $model->keluarga?->nomor_kk ?? '-', fn (Penduduk $model) => PendudukResource::getUrl('edit', ['record' => $model])))
            ->merge($this->activityFrom(ImportPendudukLog::query()->visibleToCurrentUser()->latest()->limit(6)->get(), 'Import dilakukan', fn (ImportPendudukLog $model) => $model->nama ?? $model->nik ?? 'Baris '.$model->baris, fn (ImportPendudukLog $model) => $model->alasan ?? '-'))
            ->sortByDesc('time')
            ->take(10)
            ->values()
            ->all();
    }

    private function buildKabupatenActivity(): array
    {
        return collect()
            ->merge($this->activityFrom(ImportPendudukLog::query()->latest()->limit(8)->get(), 'Import', fn (ImportPendudukLog $model) => $model->nama ?? $model->nik ?? 'Baris '.$model->baris, fn (ImportPendudukLog $model) => $model->alasan ?? '-'))
            ->merge($this->activityFrom(Penduduk::query()->inApprovedBatch()->latest()->limit(8)->get(), 'Penduduk', fn (Penduduk $model) => $model->nama_lengkap, fn (Penduduk $model) => $model->keluarga?->nomor_kk ?? '-'))
            ->merge($this->activityFrom(Rumah::query()->inApprovedBatch()->latest()->limit(8)->get(), 'Rumah', fn (Rumah $model) => $model->kode_rumah, fn (Rumah $model) => $model->alamat_lengkap))
            ->merge($this->activityFrom(AuditTrail::query()->latest()->limit(8)->get(), 'Audit', fn (AuditTrail $model) => $model->field.' - '.$model->aksi, fn (AuditTrail $model) => $model->pengguna ?? '-'))
            ->sortByDesc('time')
            ->take(12)
            ->values()
            ->all();
    }

    private function buildOperatorActivity(bool $isSekolah): array
    {
        $records = $isSekolah
            ? DataPendidikanBulanan::query()->visibleToCurrentUser()->latest()->limit(10)->get()
            : DataKesehatanBulanan::query()->visibleToCurrentUser()->latest()->limit(10)->get();

        return $this->activityFrom(
            $records,
            $isSekolah ? 'Pendidikan Bulanan' : 'Kesehatan Bulanan',
            fn ($model) => $isSekolah ? $model->sekolah : $model->puskesmas,
            fn ($model) => ($model->periode_bulan.'/'.$model->periode_tahun).' - '.$model->status
        )->values()->all();
    }

    private function mapData(Builder $query, int $limit): array
    {
        $all = (clone $query)->count();
        $base = (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);

        $markers = (clone $base)
            ->with(['desa', 'keluarga.penduduk'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Rumah $model): array => [
                'kode_rumah' => $model->kode_rumah,
                'alamat' => $model->alamat_lengkap,
                'latitude' => (float) $model->latitude,
                'longitude' => (float) $model->longitude,
                'jumlah_kk' => $model->keluarga->count(),
                'jumlah_penduduk' => $model->keluarga->sum(fn (Keluarga $keluarga) => $keluarga->penduduk->count()),
                'status_rumah' => $model->status_rumah ?: 'Lainnya',
                'status_key' => \Illuminate\Support\Str::slug($model->status_rumah ?: 'Lainnya', '_'),
                'desa' => $model->desa?->nama,
            ]);

        $statusCounts = $markers->countBy('status_rumah')->map(fn (int $count) => $count)->all();
        $avgResidents = $markers->count() > 0 ? round($markers->avg('jumlah_penduduk'), 1) : 0;

        return [
            'count' => $base->count(),
            'total' => $all,
            'unmapped' => max(0, $all - $base->count()),
            'average_residents' => $avgResidents,
            'status_counts' => $statusCounts,
            'markers' => $markers->values()->all(),
        ];
    }

    private function chartData(Builder $query, string $column, ?array $order, string $label, string $type, int $limit = 12): array
    {
        $data = (clone $query)
            ->inApprovedBatch()
            ->selectRaw("COALESCE(NULLIF({$column}, ''), 'Tidak Diisi') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit($limit)
            ->pluck('total', 'label')
            ->all();

        if ($order) {
            $ordered = [];

            foreach ($order as $item) {
                $ordered[$item] = (int) ($data[$item] ?? 0);
            }

            foreach ($data as $item => $value) {
                if (! array_key_exists($item, $ordered)) {
                    $ordered[$item] = (int) $value;
                }
            }

            $data = $ordered;
        }

        return [
            'type' => $type,
            'label' => $label,
            'data' => collect($data)->map(fn ($value) => (int) $value)->all(),
        ];
    }

    private function growthChartData(): array
    {
        $data = Penduduk::query()
            ->inApprovedBatch()
            ->selectRaw('YEAR(created_at) as label, COUNT(*) as total')
            ->groupByRaw('YEAR(created_at)')
            ->orderBy('label')
            ->pluck('total', 'label')
            ->mapWithKeys(fn ($value, $label) => [(string) $label => (int) $value])
            ->all();

        return [
            'type' => 'line',
            'label' => 'Pertumbuhan Penduduk',
            'data' => $data,
        ];
    }

    private function kabupatenKeluargaQuery(): Builder
    {
        return Keluarga::query();
    }

    private function incomeChartData(Builder $query): array
    {
        $data = (clone $query)->inApprovedBatch()->selectRaw("CASE WHEN COALESCE(penghasilan, 0) = 0 THEN 'Tidak ada penghasilan' WHEN penghasilan < 2000000 THEN '< Rp2 juta' WHEN penghasilan < 5000000 THEN 'Rp2–5 juta' ELSE '≥ Rp5 juta' END as label, COUNT(*) as total")->groupBy('label')->orderByDesc('total')->pluck('total', 'label')->map(fn ($value) => (int) $value)->all();
        return ['type' => 'doughnut', 'label' => 'Kelompok Penghasilan', 'data' => $data];
    }

    private function diseaseChartData(): array
    {
        $counts = collect();

        DataKesehatanBulanan::query()
            ->inApprovedBatch()
            ->get(['kasus_penyakit'])
            ->each(function (DataKesehatanBulanan $row) use ($counts): void {
                foreach ((array) $row->kasus_penyakit as $disease) {
                    if (is_string($disease) && $disease !== '') {
                        $counts[$disease] = ($counts[$disease] ?? 0) + 1;
                    }
                }
            });

        $counts = $counts->sortDesc()->take(10);

        return [
            'type' => 'bar',
            'data' => ['labels' => $counts->keys()->values()->all(), 'datasets' => [[
                'label' => 'Laporan kasus',
                'data' => $counts->values()->all(),
                'backgroundColor' => ['#dc2626', '#f97316', '#eab308', '#7c3aed', '#0891b2', '#16a34a'],
                'borderRadius' => 8,
            ]]],
            'options' => ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]]],
        ];
    }

    private function activityFrom($records, string $badge, callable $title, callable $meta, ?callable $link = null): Collection
    {
        return $records->map(fn ($model): array => [
            'badge' => $badge,
            'title' => $title($model),
            'meta' => $meta($model),
            'time' => $this->parseCreatedAt($model->created_at),
            'time_label' => $this->parseCreatedAt($model->created_at)?->format('d M Y H:i') ?? '-',
            'link' => $link ? $link($model) : null,
        ]);
    }

    private function parseCreatedAt($value): ?\Illuminate\Support\Carbon
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value instanceof Carbon ? $value : Carbon::instance($value);
        }

        return Carbon::parse($value);
    }

    private function kabupatenPendudukQuery(): Builder
    {
        return Penduduk::query()->inApprovedBatch();
    }

    private function approvedCollectionCount(Collection $records): int
    {
        return $records->filter(fn ($model): bool => $model->latestSubmissionBatchItem?->submissionBatch?->status === SubmissionBatch::STATUS_APPROVED)->count();
    }

    private function kpi(string $label, int|string $value, string $description, string $color): array
    {
        return compact('label', 'value', 'description', 'color');
    }

    private function taskItem(string $label, int $count, string $description): array
    {
        return [
            'label' => $label,
            'count' => $count,
            'description' => $description,
            'status' => $this->taskStatusLabel($count),
            'color' => $this->taskStatusColor($count),
            'available' => true,
        ];
    }

    private function unavailableTask(string $label): array
    {
        return [
            'label' => $label,
            'count' => null,
            'description' => 'Belum tersedia pada struktur data.',
            'status' => 'Tidak tersedia',
            'color' => 'gray',
            'available' => false,
        ];
    }

    private function action(string $label, string $description, string $url, string $color): array
    {
        return compact('label', 'description', 'url', 'color');
    }

    private function quickStat(string $title, array $chart): array
    {
        return compact('title', 'chart');
    }

    private function statCard(string $label, int $value, string $trend, string $trendIcon, string $description, string $color): array
    {
        return compact('label', 'value', 'trend', 'trendIcon', 'description', 'color');
    }

    private function statusSummaryCard(string $label, int $count, string $color): array
    {
        return compact('label', 'count', 'color');
    }

    private function buildLatestNotifications(): array
    {
        return SubmissionBatch::query()
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(fn (SubmissionBatch $batch): array => [
                'title' => $batch->submission_number.' - '.SubmissionBatch::moduleLabel($batch->module),
                'message' => 'Status: '.SubmissionBatch::statusLabel($batch->status).' | Kecamatan: '.($batch->kecamatan?->nama ?? '-'),
                'time_label' => $batch->submitted_at?->format('d M Y H:i') ?? $batch->created_at?->format('d M Y H:i') ?? '-',
                'url' => null,
            ])
            ->all();
    }

    private function buildSystemAlerts(): array
    {
        $pendingBatches = SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_PENDING)->count();
        $overdueBatches = SubmissionBatch::query()
            ->where('status', SubmissionBatch::STATUS_PENDING)
            ->whereDate('submitted_at', '<', now()->subDays(7)->toDateString())
            ->count();
        $unreportedKecamatan = max(0, Kecamatan::query()->count() - DataPendidikanBulanan::query()->inApprovedBatch()->where('periode_bulan', now()->month)->where('periode_tahun', now()->year)->distinct('kecamatan_id')->count('kecamatan_id'));

        return [
            ['title' => 'Batch Menunggu Verifikasi', 'description' => "{$pendingBatches} batch masih menunggu verifikasi.", 'severity' => 'warning'],
            ['title' => 'Batch Lama', 'description' => "{$overdueBatches} batch belum diverifikasi selama lebih dari 7 hari.", 'severity' => 'danger'],
            ['title' => 'Kecamatan Belum Lapor', 'description' => "{$unreportedKecamatan} kecamatan belum melaporkan pendidikan bulan ini.", 'severity' => 'info'],
            ['title' => 'Sistem Operasional', 'description' => 'Semua layanan inti berjalan normal.', 'severity' => 'success'],
        ];
    }

    private function progressItem(string $label, int $done, int $total): array
    {
        $percent = $this->percent($done, $total);

        return [
            'label' => $label,
            'done' => $done,
            'total' => $total,
            'percent' => $percent,
            'status' => $this->statusLabel($percent),
            'color' => $this->statusColor($percent),
        ];
    }

    private function percent(int $done, int $total): int
    {
        return $total > 0 ? (int) round(($done / $total) * 100) : 0;
    }

    private function overallProgress(array $items): int
    {
        return $items === []
            ? 0
            : (int) round(collect($items)->avg(fn (array $item): int => (int) ($item['percent'] ?? $this->percent((int) ($item['done'] ?? 0), (int) ($item['total'] ?? 0)))));
    }

    private function completionText(int $done, int $total): string
    {
        return $done.' selesai dari '.$total;
    }

    private function statusLabel(int $percent): string
    {
        return match (true) {
            $percent >= 80 => 'Aman',
            $percent >= 50 => 'Perlu Perhatian',
            default => 'Mendesak',
        };
    }

    private function statusColor(int $percent): string
    {
        return match (true) {
            $percent >= 80 => 'success',
            $percent >= 50 => 'warning',
            default => 'danger',
        };
    }

    private function taskStatusLabel(int $count): string
    {
        return match (true) {
            $count === 0 => 'Aman',
            $count <= 10 => 'Perlu Perhatian',
            default => 'Mendesak',
        };
    }

    private function taskStatusColor(int $count): string
    {
        return match (true) {
            $count === 0 => 'success',
            $count <= 10 => 'warning',
            default => 'danger',
        };
    }
}
