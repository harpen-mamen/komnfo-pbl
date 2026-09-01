<?php

namespace App\Filament\Resources\FasilitasKesehatanResource\Pages;

use App\Filament\Resources\FasilitasKesehatanResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationGroup;

class ViewFasilitasKesehatan extends ViewRecord
{
    protected static string $resource = FasilitasKesehatanResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getRelationManagers(): array
    {
        return [];
    }
}
