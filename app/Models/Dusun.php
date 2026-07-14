<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dusun extends Model
{
    use HasFactory;

    protected $table = 'dusuns';

    protected $fillable = ['desa_id', 'kode', 'nama'];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }
}
