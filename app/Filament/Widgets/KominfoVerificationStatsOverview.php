<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\SubmissionBatch;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;
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
                Stat::make('Jumlah Menunggu', 0)->icon('heroicon-o-clock')->color('warning'),
                Stat::make('Jumlah Disetujui', 0)->icon('heroicon-o-check-circle')->color('success'),
                Stat::make('Jumlah Ditolak', 0)->icon('heroicon-o-x-circle')->color('danger'),
            ];
        }

        return [
            Stat::make(
                'Jumlah Menunggu',
                SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_PENDING)->count(),
            )->icon('heroicon-o-clock')->color('warning'),
            Stat::make(
                'Jumlah Disetujui',
                SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_APPROVED)->count(),
            )->icon('heroicon-o-check-circle')->color('success'),
            Stat::make(
                'Jumlah Ditolak',
                SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_REJECTED)->count(),
            )->icon('heroicon-o-x-circle')->color('danger'),
        ];
    }
}
