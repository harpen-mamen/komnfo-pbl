<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KodeReferensi extends Model
{
    use HasFactory;

    protected $table = 'kode_referensis';

    protected $fillable = [
        'jenis', 'kode', 'nama', 'deskripsi', 'parent_id',
        'standar_sumber', 'level', 'metadata', 'aktif',
    ];

    protected $casts = [
        'metadata' => 'array',
        'aktif' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeJenis($q, string $jenis)
    {
        return $q->where('jenis', $jenis);
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', true);
    }
}
