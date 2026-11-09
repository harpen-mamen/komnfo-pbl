<?php

namespace App\Filament\Resources\DynamicDatasetResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Services\DataInput\DatasetValueManager;
use Filament\Notifications\Notification;
use App\Filament\Resources\DynamicDatasetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewDynamicDatasetValue extends ViewRecord
{
    protected static string $resource = DynamicDatasetResource::class;

    public function getTitle(): string
    {
        return $this->record->dataset?->nama ?? 'Detail Data';
    }

    public function getSubheading(): ?string
    {
        $opd = $this->record->dataset?->opd?->nama ?? '-';
        $status = $this->record->status;
        return "OPD: {$opd} | Status: {$status}";
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            EditAction::make()
                ->visible(fn () => in_array($this->record->status, ['draft', 'revision'], true)),
        ];

        if (Auth::user()->isWalidata()) {
            if ($this->record->status === 'submitted') {
                $actions[] = Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function () {
                        app(DatasetValueManager::class)->verify($this->record);
                        Notification::make()->title('Diverifikasi')->success()->send();
                    });
            }
            if ($this->record->status === 'verified') {
                $actions[] = Action::make('publish')
                    ->label('Publikasikan')
                    ->icon('heroicon-o-globe-alt')
                    ->color('success')
                    ->action(function () {
                        app(DatasetValueManager::class)->publish($this->record);
                        Notification::make()->title('Dipublikasikan')->success()->send();
                    });
            }
        }

        return $actions;
    }
}
