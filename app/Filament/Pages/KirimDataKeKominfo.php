<?php

namespace App\Filament\Pages;

use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Services\SubmissionBatchService;
use App\Support\PanelAccess;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KirimDataKeKominfo extends Page
{
    public ?int $periodeBulan;
    public string $semester;
    public int $periodeTahun;
    public array $selectedModules = [];
    public ?string $notes = null;

    protected static ?string $title = 'Kirim Data ke Kominfo';
    protected static string $view = 'filament.pages.kirim-data-kominfo';
    protected static ?string $navigationIcon = 'heroicon-o-upload';
    protected static ?int $navigationSort = 50;

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::isAdminKecamatan(), 403);

        $this->periodeBulan = now()->month;
        $this->semester = now()->month <= 6 ? 'ganjil' : 'genap';
        $this->periodeTahun = now()->year;
        $this->selectedModules = array_keys(app(SubmissionBatchService::class)->moduleDefinitions());
    }

    protected function getViewData(): array
    {
        $user = PanelAccess::user();
        $kecamatanId = $user?->kecamatan_id;
        $service = app(SubmissionBatchService::class);

        $rumahs = Rumah::query()->whereHas('desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId));
        $keluargas = Keluarga::query()->whereHas('rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId));
        $penduduks = Penduduk::query()->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId));

        $lastBatches = SubmissionBatch::query()->where('kecamatan_id', $kecamatanId)->latest('submitted_at')->limit(5)->get();
        $preview = $service->preview($kecamatanId, $this->periodeBulan, $this->semester, $this->periodeTahun);

        return [
            'total_rumah' => $rumahs->count(),
            'total_keluarga' => $keluargas->count(),
            'total_penduduk' => $penduduks->count(),
            'last_submissions' => $lastBatches,
            'modules' => $service->moduleDefinitions(),
            'preview' => $preview,
        ];
    }

    public function submit()
    {
        $user = PanelAccess::user();
        $kecamatanId = $user?->kecamatan_id;

        try {
            $batches = app(SubmissionBatchService::class)->submit($kecamatanId, $this->selectedModules, $this->periodeBulan, $this->semester, $this->periodeTahun, $this->notes, $user);

            Notification::make()
                ->title('Batch pengiriman berhasil dibuat')
                ->body('Batch modul data telah dikirim ke Kominfo dan siap diverifikasi.')
                ->success()
                ->send();

            return redirect()->route('filament.pages.dashboard-operasional');
        } catch (\Throwable $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            Notification::make()
                ->title('Gagal membuat batch')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }
}
