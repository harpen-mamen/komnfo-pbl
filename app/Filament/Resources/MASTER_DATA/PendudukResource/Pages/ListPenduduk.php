<?php

namespace App\Filament\Resources\MASTER_DATA\PendudukResource\Pages;

use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MASTER_DATA\PendudukResource;
use App\Support\UserScope;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;

abstract class ListPenduduk extends ListRecords
{
    protected static string $resource = PendudukResource::class;

    protected function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        // Penduduk scoped via keluarga -> unit_id
        $query = parent::getEloquentQuery();
        return UserScope::applyToUnitId($query->whereHas('keluarga'), $user);
    }
}
