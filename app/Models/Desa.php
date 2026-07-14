<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends Model
{
    use HasFactory;

    protected $fillable = ['kecamatan_id', 'nama', 'kode'];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function rumah(): HasMany
    {
        return $this->hasMany(Rumah::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function dusuns(): HasMany
    {
        return $this->hasMany(Dusun::class);
    }
}
