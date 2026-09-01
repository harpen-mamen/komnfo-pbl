<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class IbuHamil extends Model
{
    use HasFactory;

    protected $table = 'ibu_hamils';

    protected $fillable = [
        'data_kesehatan_bulanan_id',
        'submission_batch_id',
        'penduduk_id',
        'desa_id',
        'kecamatan_id',
        'created_by',
        'nama',
        'nik',
        'tanggal_lahir',
        'alamat',
        'nama_suami',
        'no_telepon',
        'tanggal_hpl',
        'usia_kehamilan_minggu',
        'status_kehamilan',
        'tanggal_periksa_terakhir',
        'frekuensi_periksa',
        'kategori_resiko',
        'kondisi_resiko',
        'mendapat_fe',
        'mendapat_imunisasi_tt',
        'mendapat_konseling',
        'catatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_hpl' => 'date',
        'tanggal_periksa_terakhir' => 'date',
        'mendapat_fe' => 'boolean',
        'mendapat_imunisasi_tt' => 'boolean',
        'mendapat_konseling' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (IbuHamil $model): void {
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

    public function dataKesehatan(): BelongsTo
    {
        return $this->belongsTo(DataKesehatanBulanan::class);
    }

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }

    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class);
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

    public function bayis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Bayi::class, 'ibu_id');
    }
}
