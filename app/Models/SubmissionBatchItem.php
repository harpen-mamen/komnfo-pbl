<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SubmissionBatchItem extends Model
{
    use HasFactory;

    protected $table = 'submission_batch_items';

    protected $fillable = [
        'submission_batch_id',
        'entity_type',
        'entity_id',
    ];

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
