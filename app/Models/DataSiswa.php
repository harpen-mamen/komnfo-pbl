<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataSiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_siswas';

    protected $fillable = [
        'sekolah_id',
        'submission_batch_id',
        'tahun_ajaran',
        'semester',
        'jumlah_siswa_total',
        'laki_laki',
        'perempuan',
        'siswa_diterima_baru',
        'siswa_lulus',
        'putus_sekolah',
        'naik_kelas',
        'tidak_naik',
        'siswa_mutasi_keluar',
        'siswa_mutasi_datang',
        'jumlah_rombel',
        'siswa_ABK',
        'siswa_BOS',
        'siswa_PIP',
        'siswa_KIP',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'jumlah_siswa_total' => 'integer',
        'laki_laki' => 'integer',
        'perempuan' => 'integer',
        'siswa_diterima_baru' => 'integer',
        'siswa_lulus' => 'integer',
        'putus_sekolah' => 'integer',
        'naik_kelas' => 'integer',
        'tidak_naik' => 'integer',
        'siswa_mutasi_keluar' => 'integer',
        'siswa_mutasi_datang' => 'integer',
        'jumlah_rombel' => 'integer',
        'siswa_ABK' => 'integer',
        'siswa_BOS' => 'integer',
        'siswa_PIP' => 'integer',
        'siswa_KIP' => 'integer',
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

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return UserScope::visibleTo($query);
    }

    public function getTotalBantuan(): int
    {
        return $this->siswa_BOS + $this->siswa_PIP + $this->siswa_KIP;
    }

    public function getRasioGuruSiswa(?DataGuru $dataGuru): ?float
    {
        if (!$dataGuru || $dataGuru->guru_total == 0) {
            return null;
        }
        return round($this->jumlah_siswa_total / $dataGuru->guru_total, 2);
    }

    public function getRasioLakiPerempuan(): float
    {
        if ($this->perempuan == 0) {
            return $this->laki_laki > 0 ? PHP_FLOAT_MAX : 0;
        }
        return round($this->laki_laki / $this->perempuan, 2);
    }
}
