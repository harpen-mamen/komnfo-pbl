<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetSpatialDemo extends Model
{
    protected $table = 'dataset_spatial_demos';

    protected $fillable = ['dataset_id', 'label', 'tahun', 'periode_label', 'values'];

    protected $casts = ['tahun' => 'integer', 'values' => 'array'];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }
}
