<?php

namespace App\Filament\Widgets;

use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Schema;

class KominfoVerificationStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return PanelAccess::isKominfoKabupaten();
    }

    protected function getCards(): array
    {
        if (! Schema::hasTable('submission_batches')) {
            return [
                Card::make('Jumlah Menunggu', 0)->icon('heroicon-o-clock')->color('warning'),
                Card::make('Jumlah Disetujui', 0)->icon('heroicon-o-check-circle')->color('success'),
                Card::make('Jumlah Ditolak', 0)->icon('heroicon-o-x-circle')->color('danger'),
            ];
        }

        return [
            Card::make(
                'Jumlah Menunggu',
                SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_PENDING)->count(),
            )->icon('heroicon-o-clock')->color('warning'),
            Card::make(
                'Jumlah Disetujui',
                SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_APPROVED)->count(),
            )->icon('heroicon-o-check-circle')->color('success'),
            Card::make(
                'Jumlah Ditolak',
                SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_REJECTED)->count(),
            )->icon('heroicon-o-x-circle')->color('danger'),
        ];
    }
}
