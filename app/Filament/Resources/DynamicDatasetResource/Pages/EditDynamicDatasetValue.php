<?php

namespace App\Filament\Resources\DynamicDatasetResource\Pages;

use Filament\Schemas\Schema;
use App\Services\DataInput\DatasetFieldResolver;
use App\Services\DataInput\RoadInputDefinition;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\DynamicDatasetResource;
use App\Models\Dataset;
use App\Services\DataInput\DatasetValueManager;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDynamicDatasetValue extends EditRecord
{
    public function form(Schema $schema): Schema
    {
        return $schema->components(DatasetFieldResolver::buildFormSchema($this->record->dataset));
    }

    protected static string $resource = DynamicDatasetResource::class;

    public ?Dataset $activeDataset = null;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->activeDataset = $this->record->dataset;
    }

    public function getTitle(): string
    {
        return 'Edit: ' . ($this->activeDataset?->nama ?? 'Data');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ((string)$this->record->dataset->kode === '5.59') $data['values'] = RoadInputDefinition::normalize($data['values']??[]);
        return $data;
    }

    public function getSubheading(): ?string
    {
        if (! $this->activeDataset) return null;
        $opd = $this->activeDataset->opd?->nama ?? '-';
        return "OPD: {$opd} | Status: {$this->record->status}";
    }

    public function getCancelButtonUrl(): string
    {
        $dataset = $this->activeDataset;
        if ($dataset) {
            return route('filament.admin.resources.dataset-values.index', ['dataset' => $dataset->kode]);
        }
        return parent::getCancelButtonUrl();
    }

    protected function getRedirectUrl(): string
    {
        $dataset = $this->activeDataset;
        if ($dataset) {
            return route('filament.admin.resources.dataset-values.index', ['dataset' => $dataset->kode]);
        }
        return parent::getRedirectUrl();
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('submit')
                ->label('Ajukan Verifikasi')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => $this->record->status === 'draft' && (Auth::user()?->isOpdAdmin() || Auth::user()?->isKepalaDinas()))
                ->action(function () {
                    app(DatasetValueManager::class)->submit($this->record);
                    Notification::make()
                        ->title('Data berhasil diajukan ke Walidata')
                        ->success()
                        ->send();
                }),
            Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'submitted' && Auth::user()->isWalidata())
                ->action(function () {
                    app(DatasetValueManager::class)->verify($this->record);
                    Notification::make()
                        ->title('Data diverifikasi')
                        ->success()
                        ->send();
                }),
            Action::make('publish')
                ->label('Publikasikan')
                ->icon('heroicon-o-globe-alt')
                ->color('success')
                ->visible(fn () => $this->record->status === 'verified' && Auth::user()->isWalidata())
                ->action(function () {
                    app(DatasetValueManager::class)->publish($this->record);
                    Notification::make()
                        ->title('Data dipublikasikan')
                        ->success()
                        ->send();
                }),
        ];

        if (Auth::user()?->isWalidata() && $this->record->status === 'submitted') {
            $actions[] = Action::make('request_revision')
                ->label('Minta Revisi')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->schema([
                    Textarea::make('notes')->label('Catatan revisi')->required(),
                ])
                ->action(function (array $data) {
                    app(DatasetValueManager::class)->requestRevision($this->record, $data['notes']);
                    Notification::make()->title('Revisi diminta')->warning()->send();
                });
        }

        return $actions;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['dataset_id'] = $record->dataset_id;
        $data['created_by'] = $record->created_by ?? Auth::id();
        app(DatasetValueManager::class)->updateDraft($record, $data);
        return $record->fresh();
    }
}
