<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataGuru extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_gurus';

    protected $fillable = [
        'sekolah_id',
        'submission_batch_id',
        'tahun_ajaran',
        'semester',
        'guru_total',
        'guru_laki',
        'guru_perempuan',
        'guru_PNS',
        'guru_PPPK',
        'guru_Honorer',
        'guru_S1_keatas',
        'guru_S2_keatas',
        'guru_sertifikasi',
        'guru_belum_sertifikasi',
        'GTK_Diklat_12_jam',
        'GTK_Diklat_24_jam',
        'tenaga_administrasi',
        'tenaga_perpustakaan',
        'tenaga_lab',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'guru_total' => 'integer',
        'guru_laki' => 'integer',
        'guru_perempuan' => 'integer',
        'guru_PNS' => 'integer',
        'guru_PPPK' => 'integer',
        'guru_Honorer' => 'integer',
        'guru_S1_keatas' => 'integer',
        'guru_S2_keatas' => 'integer',
        'guru_sertifikasi' => 'integer',
        'guru_belum_sertifikasi' => 'integer',
        'GTK_Diklat_12_jam' => 'integer',
        'GTK_Diklat_24_jam' => 'integer',
        'tenaga_administrasi' => 'integer',
        'tenaga_perpustakaan' => 'integer',
        'tenaga_lab' => 'integer',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'submission_batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByTahunAjaran($query, $tahun)
    {
        return $query->where('tahun_ajaran', $tahun);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeGanjil($query)
    {
        return $query->where('semester', 'ganjil');
    }

    public function scopeGenap($query)
    {
        return $query->where('semester', 'genap');
    }

    public function getPersentaseSertifikasi(): float
    {
        if ($this->guru_total == 0) {
            return 0;
        }
        return round(($this->guru_sertifikasi / $this->guru_total) * 100, 1);
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return UserScope::visibleTo($query);
    }

    public function getPersentaseS1(): float
    {
        if ($this->guru_total == 0) {
            return 0;
        }
        return round(($this->guru_S1_keatas / $this->guru_total) * 100, 1);
    }
}
