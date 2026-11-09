<?php

namespace App\Filament\Resources\MASTER_DATA\KeluargaResource\Pages;

use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MASTER_DATA\KeluargaResource;
use App\Support\UserScope;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;

abstract class ListKeluarga extends ListRecords
{
    protected static string $resource = KeluargaResource::class;

    protected function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        return UserScope::applyToUnitId(parent::getEloquentQuery(), $user);
    }
}
