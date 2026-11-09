<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $table = 'import_logs';

    protected $fillable = [
        'user_id', 'tipe', 'sumber', 'file_name',
        'total_rows', 'success_rows', 'failed_rows',
        'errors', 'payload_sample', 'status', 'finished_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'payload_sample' => 'array',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}