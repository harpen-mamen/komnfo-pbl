<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wilayah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "wilayahs";

    protected $fillable = [
        "parent_id",
        "tipe",
        "kode_bps",
        "nama",
        "singkatan",
        "kepala_wilayah",
        "latitude",
        "longitude",
        "metadata",
        "aktif",
    ];

    protected $casts = [
        "metadata" => "array",
        "aktif" => "boolean",
    ];

    public const TIPE_KABUPATEN = "kabupaten";
    public const TIPE_KECAMATAN = "kecamatan";
    public const TIPE_DESA = "desa";
    public const TIPE_KAMPUNG = "kampung";
    public const TIPE_DUSUN = "dusun";

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, "parent_id");
    }

    public function children(): HasMany
    {
        return $this->hasMany(Wilayah::class, "parent_id");
    }

    /**
     * Get rumah records that belong to this wilayah (when tipe = 'desa').
     */
    public function rumah(): HasMany
    {
        return $this->hasMany(Rumah::class, 'desa_id');
    }

    public function getFullNameAttribute(): string
    {
        $names = [$this->nama];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($names, $parent->nama);
            $parent = $parent->parent;
        }
        return implode(", ", $names);
    }
}
