<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Desa;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ImportPendudukLog;
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
            Stat::make('Total Kabupaten', Kabupaten::query()->count())->icon('heroicon-o-building-office')->color('primary'),
            Stat::make('Total Kecamatan', Kecamatan::query()->count())->icon('heroicon-o-map-pin')->color('success'),
            Stat::make('Total Desa', Desa::query()->count())->icon('heroicon-o-map')->color('warning'),
            Stat::make('Total User', User::query()->count())->icon('heroicon-o-users')->color('primary'),
            Stat::make('Total Role', Role::query()->count())->icon('heroicon-o-shield-check')->color('success'),
            Stat::make('Total Permission', Permission::query()->count())->icon('heroicon-o-key')->color('warning'),
            Stat::make('Total Audit', AuditTrail::query()->count())->icon('heroicon-o-clipboard-document-list')->color('danger'),
            Stat::make('Total Import', ImportPendudukLog::query()->count())->icon('heroicon-o-arrow-up-tray')->color('gray'),
            Stat::make('Batch Draft', $batchDraft)->icon('heroicon-o-pencil')->color('gray'),
            Stat::make('Batch Menunggu Verifikasi', $batchWaiting)->icon('heroicon-o-clock')->color('warning'),
            Stat::make('Batch Disetujui', $batchApproved)->icon('heroicon-o-check-circle')->color('success'),
            Stat::make('Total Penduduk', Penduduk::query()->inApprovedBatch()->count())->icon('heroicon-o-identification')->color('primary'),
            Stat::make('Total Rumah', Rumah::query()->inApprovedBatch()->count())->icon('heroicon-o-home')->color('success'),
            Stat::make('Total KK', Keluarga::query()->inApprovedBatch()->count())->icon('heroicon-o-user-group')->color('warning'),
        ];
    }
}
