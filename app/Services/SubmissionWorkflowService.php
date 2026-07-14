<?php

namespace App\Services;

use App\Models\SubmissionBatch;
use App\Models\User;
use Illuminate\Support\Collection;

class SubmissionWorkflowService
{
    public function submit(SubmissionBatch $batch, ?User $actor, ?string $notes = null): SubmissionBatch
    {
        return app(BatchApprovalService::class)->submit($batch, $actor, $notes, true);
    }
}

