<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

class Keluarga extends Model
{
    use HasFactory;
    use HasSubmissionBatchApproval;

    public const STATUS_EKONOMI = [
        'Miskin' => 'Miskin',
        'Menengah' => 'Menengah',
        'Mampu' => 'Mampu',
    ];

    protected $table = 'keluarga';

    protected $fillable = [
        'rumah_id',
        'nomor_kk',
        'nama_kepala_keluarga',
        'jumlah_anggota',
        'status_ekonomi',
        'created_by',
    ];

    protected $casts = [
        'jumlah_anggota' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn (Keluarga $keluarga) => $keluarga->created_by ??= Auth::id());
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        // SPEC-004: Single source of truth — UserScope::visibleTo().
        return UserScope::visibleTo($query);
    }

    public function rumah(): BelongsTo
    {
        return $this->belongsTo(Rumah::class);
    }

    public function rumahLapangan(): BelongsToMany
    {
        return $this->belongsToMany(Rumah::class, 'rumah_keluarga')
            ->withPivot('created_by')
            ->withTimestamps();
    }

    public function penduduk(): HasMany
    {
        return $this->hasMany(Penduduk::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * FASE 1: Source attribution (polymorphic).
     * KK dari Dukcapil adalah master data, bukan dibuat Desa.
     */
    public function sourceAttribution(): MorphOne
    {
        return $this->morphOne(SourceAttribution::class, 'sourceable');
    }
}
