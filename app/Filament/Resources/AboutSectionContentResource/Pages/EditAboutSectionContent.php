<?php

namespace App\Filament\Resources\AboutSectionContentResource\Pages;

use App\Filament\Resources\AboutSectionContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAboutSectionContent extends EditRecord
{
    protected static string $resource = AboutSectionContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Lihat Halaman')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => url('/tentang-daerah'))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }
}
