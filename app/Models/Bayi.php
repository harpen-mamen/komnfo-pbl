<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Bayi extends Model
{
    use HasFactory;

    protected $table = 'bayis';

    protected $fillable = [
        'submission_batch_id',
        'penduduk_id',
        'ibu_id',
        'desa_id',
        'kecamatan_id',
        'created_by',
        'nama',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'berat_badan_lahir',
        'panjang_badan_lahir',
        'imunisasi_hb0',
        'imunisasi_bcg',
        'imunisasi_dpt_hb1',
        'imunisasi_dpt_hb2',
        'imunisasi_dpt_hb3',
        'imunisasi_polio1',
        'imunisasi_polio2',
        'imunisasi_polio3',
        'imunisasi_polio4',
        'imunisasi_campak',
        'imunisasi_mmr',
        'status_gizi',
        'berat_badan_sekarang',
        'panjang_badan_sekarang',
        'kategori_balita',
        'mendapat_vitamin_a',
        'tanggal_vitamin_a',
        'mendapat_taburia',
        'asi_eksklusif',
        'catatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_vitamin_a' => 'date',
        'imunisasi_hb0' => 'boolean',
        'imunisasi_bcg' => 'boolean',
        'imunisasi_dpt_hb1' => 'boolean',
        'imunisasi_dpt_hb2' => 'boolean',
        'imunisasi_dpt_hb3' => 'boolean',
        'imunisasi_polio1' => 'boolean',
        'imunisasi_polio2' => 'boolean',
        'imunisasi_polio3' => 'boolean',
        'imunisasi_polio4' => 'boolean',
        'imunisasi_campak' => 'boolean',
        'imunisasi_mmr' => 'boolean',
        'mendapat_vitamin_a' => 'boolean',
        'mendapat_taburia' => 'boolean',
        'asi_eksklusif' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Bayi $model): void {
            if ($model->created_by === null) {
                $model->created_by = Auth::id();
            }
        });
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        // SPEC-004: Single source of truth — UserScope::visibleTo().
        return UserScope::visibleTo($query);
    }

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }

    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class);
    }

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(IbuHamil::class, 'ibu_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
