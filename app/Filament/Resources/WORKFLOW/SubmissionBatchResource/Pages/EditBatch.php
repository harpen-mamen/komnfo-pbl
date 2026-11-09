<?php

namespace App\Filament\Resources\WORKFLOW\SubmissionBatchResource\Pages;

use App\Filament\Resources\WORKFLOW\SubmissionBatchResource;
use Filament\Resources\Pages\EditRecord;

abstract class EditBatch extends EditRecord
{
    protected static string $resource = SubmissionBatchResource::class;
}
