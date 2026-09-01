<?php

namespace App\Filament\Pages;

use App\Models\ImportPendudukLog;
use App\Services\PendudukImportService;
use App\Support\AdminKecamatan;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class ImportDataPenduduk extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-upload';
    protected static ?string $navigationLabel = 'Import Penduduk';
    protected static ?string $title = 'Import Data Penduduk';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.import-data-penduduk';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel(), 403);

        $this->form->fill();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::canAccessKabupatenPanel() || PanelAccess::canAccessKecamatanPanel();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\FileUpload::make('file')
                ->label('File Excel (.xls/.xlsx) atau CSV')
                ->disk('local')
                ->directory('imports')
                ->maxSize(10240)
                ->acceptedFileTypes([
                    'text/csv',
                    'text/plain',
                    'text/tab-separated-values',
                    'application/csv',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->required(),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function import(PendudukImportService $service): void
    {
        $state = $this->form->getState();
        $relativePath = $state['file'];
        $path = Storage::disk('local')->path($relativePath);
        $result = $service->import($path);

        $this->notify('success', "{$result['created']} penduduk berhasil diimport, {$result['failed']} baris gagal.");
        $this->form->fill();
    }

    public function getRecentFailuresProperty()
    {
        return ImportPendudukLog::query()
            ->visibleToCurrentUser()
            ->latest()
            ->limit(20)
            ->get();
    }
}
