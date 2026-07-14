<?php

namespace App\Filament\Widgets;

use App\Models\AuditTrail;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\SubmissionBatch;
use App\Models\User;
use App\Support\PanelAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Schema;

class SuperAdminStatsOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return PanelAccess::isSuperAdmin();
    }

    protected function getCards(): array
    {
        $batchDraft = 0;
        $batchWaiting = 0;
        $batchApproved = 0;

        if (Schema::hasTable('submission_batches')) {
            $batchDraft = SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_DRAFT)->count();
            $batchWaiting = SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_PENDING)->count();
            $batchApproved = SubmissionBatch::query()->where('status', SubmissionBatch::STATUS_APPROVED)->count();
        }

        return [
            Card::make('Total Kabupaten', Kabupaten::query()->count())->icon('heroicon-o-office-building')->color('primary'),
            Card::make('Total Kecamatan', Kecamatan::query()->count())->icon('heroicon-o-location-marker')->color('success'),
            Card::make('Total Desa', \App\Models\Desa::query()->count())->icon('heroicon-o-map')->color('warning'),
            Card::make('Total User', User::query()->count())->icon('heroicon-o-users')->color('primary'),
            Card::make('Total Role', \Spatie\Permission\Models\Role::query()->count())->icon('heroicon-o-shield-check')->color('success'),
            Card::make('Total Permission', \Spatie\Permission\Models\Permission::query()->count())->icon('heroicon-o-key')->color('warning'),
            Card::make('Total Audit', AuditTrail::query()->count())->icon('heroicon-o-clipboard-list')->color('danger'),
            Card::make('Total Import', \App\Models\ImportPendudukLog::query()->count())->icon('heroicon-o-upload')->color('gray'),
            Card::make('Batch Draft', $batchDraft)->icon('heroicon-o-pencil')->color('gray'),
            Card::make('Batch Menunggu Verifikasi', $batchWaiting)->icon('heroicon-o-clock')->color('warning'),
            Card::make('Batch Disetujui', $batchApproved)->icon('heroicon-o-check-circle')->color('success'),
            Card::make('Total Penduduk', Penduduk::query()->inApprovedBatch()->count())->icon('heroicon-o-identification')->color('primary'),
            Card::make('Total Rumah', Rumah::query()->inApprovedBatch()->count())->icon('heroicon-o-home')->color('success'),
            Card::make('Total KK', Keluarga::query()->inApprovedBatch()->count())->icon('heroicon-o-user-group')->color('warning'),
        ];
    }
}
