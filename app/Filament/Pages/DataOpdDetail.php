<?php

namespace App\Filament\Pages;

use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\Opd;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\RouteParameter;

class DataOpdDetail extends Page
{
    protected static ?string $slug = 'data-opd/{opd}';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $title = 'Dataset OPD';
    protected string $view = 'filament.pages.data-opd-detail';
    protected static bool $shouldRegisterNavigation = false;

    public ?Opd $opdModel = null;

    public function mount(#[RouteParameter] string $opd = ''): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $this->opdModel = Opd::query()->where('aktif', true)->findOrFail($opd);
        $canViewAll = $user->hasAnyRole(['super_admin', 'admin', 'admin_kominfo', 'walidata']);
        abort_unless($canViewAll || (int) $user->opd_id === (int) $this->opdModel->id, 403);
    }

    public function getViewData(): array
    {
        $datasets = Dataset::query()
            ->with(['fields', 'values' => fn ($query) => $query
                ->where('status', DatasetValue::STATUS_PUBLISHED)
                ->orderByDesc('tahun')->orderByDesc('id')])
            ->whereIn('opd_id', $this->visibleOpdIds())
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return ['opd' => $this->opdModel, 'datasets' => $datasets];
    }

    private function visibleOpdIds(): array
    {
        $user = Auth::user();
        if ($user?->hasAnyRole(['super_admin', 'admin', 'admin_kominfo', 'walidata'])) {
            return Opd::query()->where('aktif', true)->where('nama', $this->opdModel->nama)->pluck('id')->all();
        }

        return [(int) $this->opdModel->id];
    }
}
