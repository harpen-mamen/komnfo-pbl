<?php

namespace App\Filament\Pages;

use App\Models\DatasetValue;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class KirimDataSektoralOpd extends Page
{
    protected static ?string $slug = 'kirim-data-sektoral';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Kirim Data';
    protected static string | \UnitEnum | null $navigationGroup = 'Data Sektoral';
    protected static ?string $title = 'Kirim Data ke Kominfo';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.kirim-data-sektoral-opd';

    public array $selectedValues = [];
    public ?string $notes = null;
    public int $selectedCount = 0;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->opd_id && ($user->isOpdAdmin() || $user->isKepalaDinas());
    }

    public function mount(): void
    {
        abort_unless(static::shouldRegisterNavigation(), 403);
        $this->selectedCount = (clone $this->readyValues())->count();
    }

    protected function getViewData(): array
    {
        $values = $this->readyValues()->with(['dataset:id,nama,kode'])->latest('updated_at')->limit(100)->get(['id', 'dataset_id', 'tahun', 'periode_label', 'wilayah_type', 'wilayah_id', 'values', 'status', 'updated_at']);

        return [
            'values' => $values,
            'opd' => Auth::user()->opd?->nama ?? 'OPD',
            'selected_count' => $this->selectedCount,
            'total_ready' => $this->selectedCount,
        ];
    }

    public function selectAll(): void
    {
        $this->selectedCount = (clone $this->readyValues())->count();
        $this->selectedValues = [];
    }

    public function clearSelection(): void
    {
        $this->selectedValues = [];
        $this->selectedCount = 0;
    }

    private function readyValues()
    {
        return DatasetValue::query()
            ->whereHas('dataset', fn ($query) => $query->where('opd_id', Auth::user()->opd_id))
            ->whereIn('status', [DatasetValue::STATUS_DRAFT, DatasetValue::STATUS_REVISION]);
    }
}
