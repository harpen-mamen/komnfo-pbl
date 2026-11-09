<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatasetSubmission extends Model
{
    protected $fillable = ['opd_id', 'created_by', 'status', 'notes', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function values(): HasMany
    {
        return $this->hasMany(DatasetValue::class, 'dataset_submission_id');
    }
}
