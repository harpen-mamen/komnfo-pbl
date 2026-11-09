<?php

namespace App\Filament\Pages;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\FileUpload;
use App\Models\ImportPendudukLog;
use App\Services\PendudukImportService;
use App\Support\PanelAccess;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

abstract class ImportDataPenduduk extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Import Penduduk';
    protected static ?string $title = 'Import Data Penduduk';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.import-data-penduduk';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner(), 403);

        $this->form->fill();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return PanelAccess::isSuperAdmin() || PanelAccess::isKependudukanDataOwner();
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('file')
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

        \Filament\Notifications\Notification::make()->success()->title("{$result['created']} penduduk berhasil diimport, {$result['failed']} baris gagal.")->send();
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
