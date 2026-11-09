<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class PelayananKesehatan extends Model
{
    use HasFactory;

    protected $table = 'pelayanan_kesehatans';

    protected $fillable = [
        'submission_batch_id',
        'penduduk_id',
        'desa_id',
        'kecamatan_id',
        'created_by',
        'nama_pasien',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telepon',
        'jenis_pelayanan',
        'tanggal_pelayanan',
        'nama_pelayanan',
        'diagnosa',
        'tindakan',
        'tenaga_kesehatan_yang_melayani',
        'hasil_pemeriksaan',
        'resep',
        'catatan',
        'jenis_imunisasi',
        'tanggal_imunisasi',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_pelayanan' => 'date',
        'tanggal_imunisasi' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (PelayananKesehatan $model): void {
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

    // dataKesehatan relationship removed - DataKesehatanBulanan table deleted
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
}
