<?php

namespace App\Filament\Resources\DynamicDatasetResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use App\Imports\DatasetValueImport;
use App\Imports\DatasetValueCsvImport;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use App\Filament\Pages\ImportGisDataset;
use App\Models\DatasetValue;
use Filament\Actions\CreateAction;
use App\Filament\Pages\InfografisDataset;
use App\Filament\Resources\DynamicDatasetResource\Widgets\DatasetSelectorWidget;
use App\Filament\Resources\DynamicDatasetResource;
use App\Models\Dataset;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Halaman list untuk dataset value - dengan dukungan:
 * - Quick create new entry
 * - Switch dataset (pilih_dataset)
 * - View infografis
 * - Export CSV/Excel/PDF
 * - Import data dari Excel
 * - Submit/Verify/Publish per-row (di table)
 */
class ListDynamicDatasetValues extends ListRecords
{
    protected static string $resource = DynamicDatasetResource::class;

    public ?Dataset $activeDataset = null;

    public function mount(): void
    {
        parent::mount();
        $kode = request('dataset');
        if ($kode) {
            $user = Auth::user();
            $this->activeDataset = Dataset::with('opd')->where('kode', $kode)
                ->when($user && ! ($user->isSuperAdmin() || $user->isWalidata()), fn ($query) => $query->where('opd_id', $user->opd_id ?? -1))
                ->first();
        }
    }

    public function getTitle(): string
    {
        if ($this->activeDataset) {
            return $this->activeDataset->nama;
        }
        return 'Data Sektoral';
    }

    public function getSubheading(): ?string
    {
        if (! $this->activeDataset) return null;
        $opd = $this->activeDataset->opd?->nama ?? '-';
        $desc = $this->activeDataset->deskripsi ?? '';
        return "OPD: {$opd} | {$desc}";
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('pilih_dataset')
                ->label('Pilih Dataset')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.dataset-values.index'))
                ->visible(fn () => $this->activeDataset !== null),

            Action::make('import_template')
                ->label('Template Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->url(fn () => route('dataset.import.template', ['kode' => $this->activeDataset?->kode]))
                ->openUrlInNewTab()
                ->visible(fn () => $this->activeDataset !== null),

            Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-cloud-arrow-up')
                ->color('success')
                ->visible(fn () => $this->activeDataset !== null)
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel/CSV')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                        ->maxSize(10240)
                        ->required()
                        ->helperText('Upload file Excel dengan kolom: tahun, periode_label, [kecamatan_id jika perlu], values_*'),
                    Toggle::make('auto_publish')
                        ->label('Auto-publish setelah import')
                        ->helperText('Hanya untuk Walidata/Super Admin. Otomatis approve dan publish data.')
                        ->visible(fn () => Auth::user()->isWalidata()),
                ])
                ->action(function (array $data) {
                    if (! isset($data['file'])) {
                        Notification::make()->title('File wajib diisi')->danger()->send();
                        return;
                    }
                    $filePath = Storage::disk('local')->path($data['file']);
                    if (! file_exists($filePath)) {
                        Notification::make()->title('File tidak ditemukan')->danger()->send();
                        return;
                    }

                    try {
                        $isCsv = in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['csv', 'txt'], true);
                        $importClass = $isCsv ? DatasetValueCsvImport::class : DatasetValueImport::class;
                        $import = new $importClass(
                            $this->activeDataset,
                            Auth::user(),
                            $data['auto_publish'] ?? false
                        );
                        if ($isCsv) {
                            $import->import($filePath);
                        } else {
                            Excel::import($import, $filePath);
                        }
                        $result = $import->result;

                        if ($result['failed'] > 0 || $result['errors'] !== []) {
                            Notification::make()
                                ->title("Import selesai: {$result['success']} berhasil, {$result['failed']} gagal")
                                ->body(implode("\n", array_slice($result['errors'], 0, 5)))
                                ->warning()
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->title("Berhasil import {$result['success']} data")
                                ->success()
                                ->send();
                        }
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Error import: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }
                }),
        ];

        if ($this->activeDataset) {
            if ($this->activeDataset->fields()->where('tipe', 'json')->where('lookup_source', 'like', 'geometry:%')->exists()) {
                $actions[] = Action::make('import_gis')
                    ->label('Impor GIS / SHP')->icon('heroicon-o-map')->color('primary')
                    ->url(fn () => ImportGisDataset::getUrl(['kode' => $this->activeDataset->kode]))
                    ->visible(fn () => Auth::user()?->can('create', DatasetValue::class));
            }
            $actions[] = CreateAction::make()
                ->label('Input Data Baru')
                ->visible(fn () => Auth::user()?->can('create', DatasetValue::class))
                ->url(fn () => route('filament.admin.resources.dataset-values.create', [
                    'dataset' => $this->activeDataset->kode,
                ]));

            $actions[] = Action::make('infografis')
                ->label('Infografis')
                ->icon('heroicon-o-chart-bar')
                ->color('warning')
                ->url(fn () => InfografisDataset::getUrl(['kode' => $this->activeDataset->kode]));

            $actions[] = Action::make('export_csv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('dataset.export', ['kode' => $this->activeDataset->kode, 'format' => 'csv']))
                ->openUrlInNewTab();
        }

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        if (! $this->activeDataset) {
            return [
                DatasetSelectorWidget::class,
            ];
        }
        return [];
    }
}
