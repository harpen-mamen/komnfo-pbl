<?php

namespace App\Filament\Pages;

use App\Models\DatasetSubmission;
use App\Models\DatasetValue;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class VerifikasiDataSektoral extends Page
{
    protected static ?string $slug = 'verifikasi-data-sektoral';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Verifikasi Data OPD';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Sektoral';
    protected static ?string $title = 'Verifikasi Data OPD';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.verifikasi-data-sektoral';

    public static function getNavigationBadge(): ?string
    {
        // Navigation is built during successful login, before any page renders.
        // An incomplete installation must not prevent users from signing in.
        if (! Schema::hasTable('dataset_submissions')) {
            return null;
        }

        $count = DatasetSubmission::query()->where('status', DatasetValue::STATUS_SUBMITTED)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user() && (Auth::user()->isWalidata() || Auth::user()->isSuperAdmin());
    }

    public function mount(): void
    {
        abort_unless(static::shouldRegisterNavigation(), 403);
    }

    protected function getViewData(): array
    {
        return [
            'submissions' => DatasetSubmission::query()
                ->with(['opd:id,nama', 'createdBy:id,name', 'values.dataset:id,nama,kode'])
                ->where('status', DatasetValue::STATUS_SUBMITTED)
                ->latest('submitted_at')->limit(50)->get(['id', 'opd_id', 'created_by', 'status', 'submitted_at', 'notes']),
        ];
    }

}
