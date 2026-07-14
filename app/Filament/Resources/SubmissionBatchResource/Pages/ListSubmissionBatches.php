<?php

namespace App\Filament\Resources\SubmissionBatchResource\Pages;

use App\Filament\Resources\SubmissionBatchResource;
use App\Filament\Widgets\KominfoVerificationStatsOverview;
use Filament\Resources\Pages\ListRecords;

class ListSubmissionBatches extends ListRecords
{
    protected static string $resource = SubmissionBatchResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            KominfoVerificationStatsOverview::class,
        ];
    }
}
