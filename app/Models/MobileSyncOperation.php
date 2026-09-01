<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileSyncOperation extends Model
{
    protected $fillable = [
        'operation_id',
        'user_id',
        'device_id',
        'dataset',
        'local_id',
        'base_version',
        'payload',
        'status',
        'server_id',
        'batch_id',
        'conflict_reason',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'batch_id');
    }
}
