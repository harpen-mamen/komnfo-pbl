<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileFieldPoint extends Model
{
    protected $table = 'mobile_field_points';

    protected $fillable = [
        'operation_id', 'desa_id', 'jenis', 'kode', 'nama', 'alamat',
        'latitude', 'longitude', 'attributes', 'keluarga_dataset_value_ids',
        'status', 'submission_batch_id', 'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'attributes' => 'array',
        'keluarga_dataset_value_ids' => 'array',
    ];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'desa_id');
    }

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }
}
