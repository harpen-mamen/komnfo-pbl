<?php

namespace App\Filament\Resources\WORKFLOW\SubmissionBatchResource\Pages;

use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\WORKFLOW\SubmissionBatchResource;
use App\Support\UserScope;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;

abstract class ListBatch extends ListRecords
{
    protected static string $resource = SubmissionBatchResource::class;

    protected function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        // Walidata/Pembina: see all batches
        if ($user->isWalidata() || $user->isPembina()) {
            return parent::getEloquentQuery();
        }

        // Others: only batches from their unit scope
        $query = parent::getEloquentQuery();
        $visibleUnitIds = UserScope::getVisibleUnitIds($user);
        return $query->whereIn('unit_id', $visibleUnitIds);
    }
}
