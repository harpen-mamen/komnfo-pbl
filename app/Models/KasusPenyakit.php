<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasusPenyakit extends Model
{
    protected $fillable = ['penduduk_id', 'desa_id', 'kecamatan_id', 'submission_batch_id', 'jenis_penyakit', 'tanggal_diagnosis', 'status_kasus', 'catatan', 'created_by'];
    protected $casts = ['tanggal_diagnosis' => 'date'];

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        // SPEC-004: Single source of truth — UserScope::visibleTo().
        return UserScope::visibleTo($query);
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
    // dataKesehatan relationship removed - DataKesehatanBulanan table deleted
    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
