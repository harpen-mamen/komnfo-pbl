<?php

namespace App\Filament\Resources\AboutPageSettingResource\Pages;

use App\Filament\Resources\AboutPageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAboutPageSetting extends EditRecord
{
    protected static string $resource = AboutPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('Lihat Halaman Publik')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => url('/tentang-daerah'))
                ->openUrlInNewTab(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();
        return $data;
    }
}
