<?php

namespace App\Filament\Pages;

use App\Filament\Resources\SubmissionBatchResource;
use App\Models\MobileFieldPoint;
use App\Models\SubmissionBatch;
use App\Models\Wilayah;
use App\Support\PanelAccess;
use Filament\Pages\Page;

class KirimDataKeKominfo extends Page
{
    protected static ?string $navigationLabel = 'Kiriman Mobile';
    protected static ?string $title = 'Kiriman Mobile Kecamatan';
    protected string $view = 'filament.pages.kirim-data-kominfo';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?int $navigationSort = 50;

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isAdminKecamatan();
    }

    public function mount(): void
    {
        abort_unless(PanelAccess::isAdminKecamatan(), 403);

    }

    protected function getViewData(): array
    {
        $user = PanelAccess::user();
        $kecamatanId = (int) ($user?->kecamatan_id ?: $user?->desa?->parent_id);

        if (! $kecamatanId) {
            return [
                'kecamatan' => null,
                'totalPoints' => 0,
                'pendingBatches' => 0,
                'inReviewBatches' => 0,
                'forwardedBatches' => 0,
                'points' => collect(),
                'hasVillages' => false,
            ];
        }

        $desaIds = Wilayah::query()
            ->where('parent_id', $kecamatanId)
            ->where('tipe', Wilayah::TIPE_DESA)
            ->pluck('id');

        $mobileBatches = SubmissionBatch::query()
            ->where('kecamatan_id', $kecamatanId)
            ->where('module', SubmissionBatch::MODULE_LAPANGAN)
            ->where('payload_snapshot->source', 'mobile');

        $points = MobileFieldPoint::query()
            ->with(['desa:id,nama,parent_id', 'submissionBatch:id,submission_number,module,status,submitted_at,forwarded_at'])
            ->whereIn('desa_id', $desaIds)
            ->latest()
            ->limit(50)
            ->get();

        return [
            'kecamatan' => Wilayah::query()->whereKey($kecamatanId)->value('nama'),
            'totalPoints' => MobileFieldPoint::query()->whereIn('desa_id', $desaIds)->count(),
            'pendingBatches' => (clone $mobileBatches)->where('status', SubmissionBatch::STATUS_PENDING)->count(),
            'inReviewBatches' => (clone $mobileBatches)->where('status', SubmissionBatch::STATUS_IN_REVIEW)->whereNull('forwarded_at')->count(),
            'forwardedBatches' => (clone $mobileBatches)->whereNotNull('forwarded_at')->count(),
            'points' => $points,
            'hasVillages' => $desaIds->isNotEmpty(),
        ];
    }
}
