<?php

namespace App\Filament\Resources\DataKesehatanBulananResource\Pages;

use App\Filament\Resources\DataKesehatanBulananResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDataKesehatanBulanan extends ViewRecord
{
    protected static string $resource = DataKesehatanBulananResource::class;

    protected function getActions(): array
    {
        return [Actions\EditAction::make()];
    }
}
