<?php

namespace App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource\Pages;

use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DATA_LAPANGAN\ObjekLapanganResource;
use App\Support\UserScope;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;

abstract class ListObjekLapangan extends ListRecords
{
    protected static string $resource = ObjekLapanganResource::class;

    protected function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        // Walidata/Pembina: see all objects
        if ($user->isWalidata() || $user->isPembina()) {
            return parent::getEloquentQuery();
        }

        // Others: only objects from their unit scope
        $query = parent::getEloquentQuery();
        $visibleUnitIds = UserScope::getVisibleUnitIds($user);
        return $query->whereIn('unit_id', $visibleUnitIds);
    }
}
