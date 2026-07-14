<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\AdminKecamatan;
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
        'kode_rumah',
        'alamat_lengkap',
        'latitude',
        'longitude',
        'status_rumah',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Rumah $rumah): void {
            $rumah->created_by ??= Auth::id();
        });
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return AdminKecamatan::scopeRumahToKecamatan($query);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
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
