<?php

namespace App\Filament\Pages;

use App\Models\Dataset;
use App\Models\DatasetValue;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DataOpd extends Page
{
    protected static ?string $slug = 'data-opd';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Katalog Dataset';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Sektoral';
    protected static ?string $title = 'Katalog Dataset';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.data-opd';

    public string $search = '';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $query = Dataset::query()->with('opd:id,nama,kode')
            ->withCount(['fields', 'values', 'values as published_count' => fn ($q) => $q
                ->where('status', DatasetValue::STATUS_PUBLISHED)->whereNotNull('published_at')])
            ->where('is_active', true)->whereHas('opd', fn ($q) => $q->where('aktif', true));

        if (! ($user->isSuperAdmin() || $user->isWalidata())) {
            $query->where('opd_id', $user->opd_id ?? -1);
        }
        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(fn ($q) => $q->where('nama', 'like', $term)->orWhere('kode', 'like', $term)
                ->orWhereHas('opd', fn ($opds) => $opds->where('nama', 'like', $term)));
        }
        $datasets = $query->orderBy('opd_id')->orderBy('nama')->get();

        return [
            'grouped' => $datasets->groupBy(fn ($dataset) => $dataset->opd->nama),
            'totalDatasets' => $datasets->count(),
            'totalValues' => $datasets->sum('values_count'),
            'totalPublished' => $datasets->sum('published_count'),
            'canCreate' => $user->can('create', DatasetValue::class),
        ];
    }
}
