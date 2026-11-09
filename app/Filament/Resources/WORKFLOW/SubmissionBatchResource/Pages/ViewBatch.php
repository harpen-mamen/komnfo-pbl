<?php

namespace App\Filament\Resources\WORKFLOW\SubmissionBatchResource\Pages;

use App\Filament\Resources\WORKFLOW\SubmissionBatchResource;
use Filament\Resources\Pages\ViewRecord;

abstract class ViewBatch extends ViewRecord
{
    protected static string $resource = SubmissionBatchResource::class;
}
