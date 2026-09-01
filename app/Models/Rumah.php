<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Rumah extends Model
{
    use HasFactory;
    use HasSubmissionBatchApproval;

    public const STATUS = [
        'Milik Sendiri' => 'Milik Sendiri',
        'Kontrak' => 'Kontrak',
        'Sewa' => 'Sewa',
        'Dinas' => 'Dinas',
        'Lainnya' => 'Lainnya',
    ];

    protected $table = 'rumah';

    protected $fillable = [
        'desa_id',
        'dusun_id',
        'kode_rumah',
        'alamat_lengkap',
        'latitude',
        'longitude',
        'status_rumah',
        'keterangan',
        'publik',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'publik' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Rumah $rumah): void {
            $rumah->created_by ??= Auth::id();
        });
    }

    /**
     * Accessor: Return koordinat as array for form population (edit/view).
     */
    public function getKoordinatAttribute(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        // SPEC-004: Single source of truth — UserScope::visibleTo().
        return UserScope::visibleTo($query);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function keluarga(): HasMany
    {
        return $this->hasMany(Keluarga::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
