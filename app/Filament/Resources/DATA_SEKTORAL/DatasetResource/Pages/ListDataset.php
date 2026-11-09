<?php

namespace App\Filament\Resources\DATA_SEKTORAL\DatasetResource\Pages;

use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DATA_SEKTORAL\DatasetResource;
use App\Support\UserScope;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;

abstract class ListDataset extends ListRecords
{
    protected static string $resource = DatasetResource::class;

    protected function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        // Walidata/Pembina: see all datasets
        if ($user->isWalidata() || $user->isPembina()) {
            return parent::getEloquentQuery();
        }

        // OPD: only their own datasets
        $query = parent::getEloquentQuery();
        $visibleUnitIds = UserScope::getVisibleUnitIds($user);
        return $query->whereIn('unit_id', $visibleUnitIds);
    }
}
