<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TenagaKesehatan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tenaga_kesehatans';

    protected $fillable = [
        'data_kesehatan_bulanan_id',
        'submission_batch_id',
        'fasilitas_kesehatan_id',
        'penduduk_id',
        'desa_id',
        'kecamatan_id',
        'created_by',
        'nama',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telepon',
        'profesi',
        'str',
        'masa_berlaku_str',
        'sip',
        'masa_berlaku_sip',
        'tempat_kerja',
        'jenis_fasilitas',
        'status_kepegawaian',
        'status_tenaga_kesehatan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'masa_berlaku_str' => 'date',
        'masa_berlaku_sip' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (TenagaKesehatan $model): void {
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

    public function fasilitasKesehatan(): BelongsTo
    {
        return $this->belongsTo(FasilitasKesehatan::class, 'fasilitas_kesehatan_id');
    }
}
