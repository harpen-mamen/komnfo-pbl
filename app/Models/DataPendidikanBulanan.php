<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class DataPendidikanBulanan extends Model
{
    use HasFactory;
    use HasSubmissionBatchApproval;

    public const STATUS = [
        'Terkirim' => 'Terkirim',
        'Terverifikasi' => 'Terverifikasi',
        'Perlu Revisi' => 'Perlu Revisi',
    ];

    protected $table = 'data_pendidikan_bulanans';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'periode_pelaporan_id',
        'sekolah',
        'tahun_ajaran', 'semester', 'npsn', 'status_sekolah', 'jenjang', 'rombel',
        'kecamatan_id',
        'submission_batch_id',
        'desa_id',
        'jumlah_siswa',
        'laki_laki',
        'perempuan',
        'putus_sekolah',
        'lulus',
        'naik_kelas',
        'tidak_naik',
        'siswa_baru', 'siswa_lulus', 'siswa_pindah', 'abk', 'guru_asn', 'guru_pppk', 'guru_honorer', 'tenaga_administrasi', 'kepala_sekolah',
        'internet', 'listrik', 'air_bersih', 'perpustakaan', 'laboratorium', 'uks', 'jumlah_ruang', 'rusak_ringan', 'rusak_berat', 'program_pendidikan', 'prestasi', 'permasalahan', 'uraian_permasalahan',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'jumlah_siswa' => 'integer',
        'laki_laki' => 'integer',
        'perempuan' => 'integer',
        'putus_sekolah' => 'integer',
        'lulus' => 'integer',
        'naik_kelas' => 'integer',
        'tidak_naik' => 'integer',
        'rombel' => 'integer', 'siswa_baru' => 'integer', 'siswa_lulus' => 'integer', 'siswa_pindah' => 'integer', 'abk' => 'integer', 'guru_asn' => 'integer', 'guru_pppk' => 'integer', 'guru_honorer' => 'integer', 'tenaga_administrasi' => 'integer', 'kepala_sekolah' => 'integer', 'jumlah_ruang' => 'integer', 'rusak_ringan' => 'integer', 'rusak_berat' => 'integer',
        'internet' => 'boolean', 'listrik' => 'boolean', 'air_bersih' => 'boolean', 'perpustakaan' => 'boolean', 'laboratorium' => 'boolean', 'uks' => 'boolean', 'program_pendidikan' => 'array', 'prestasi' => 'array', 'permasalahan' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DataPendidikanBulanan $model): void {
            $model->created_by ??= Auth::id();
            $model->updated_by ??= Auth::id();
        });

        static::saving(function (DataPendidikanBulanan $model): void {
            $model->updated_by = Auth::id();
        });
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        // SPEC-004: Single source of truth — UserScope::visibleTo().
        return UserScope::visibleTo($query);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function periodePelaporan(): BelongsTo
    {
        return $this->belongsTo(PeriodePelaporan::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * SPEC-003: Link to the submission batch that this record belongs to.
     * Every OPD record is tied to an approved batch for read visibility.
     */
    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }
}
