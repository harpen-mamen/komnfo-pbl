<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekamMedis extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rekam_medis';

    protected $fillable = [
        'fasilitas_kesehatan_id',
        'penduduk_id',
        'tenaga_kesehatan_id',
        'submission_batch_id',
        'tanggal_kunjungan',
        'jenis_pelayanan',
        'poli',
        'diagnosa_utama',
        'kode_icd10',
        'diagnosa_sekunder',
        'tindakan',
        'obat_diberikan',
        'cara_bayar',
        'rujukan',
        'nomor_rujukan',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'diagnosa_sekunder' => 'array',
        'tindakan' => 'array',
        'obat_diberikan' => 'array',
    ];

    public function fasilitasKesehatan(): BelongsTo
    {
        return $this->belongsTo(FasilitasKesehatan::class, 'fasilitas_kesehatan_id');
    }

    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'penduduk_id');
    }

    public function tenagaKesehatan(): BelongsTo
    {
        return $this->belongsTo(TenagaKesehatan::class, 'tenaga_kesehatan_id');
    }

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'submission_batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('tanggal_kunjungan', [$from, $to]);
    }

    public function scopeByJenisPelayanan($query, $jenis)
    {
        return $query->where('jenis_pelayanan', $jenis);
    }

    public function scopeRawatJalan($query)
    {
        return $query->where('jenis_pelayanan', 'Rawat Jalan');
    }

    public function scopeRawatInap($query)
    {
        return $query->where('jenis_pelayanan', 'Rawat Inap');
    }

    public function scopeByIcd10($query, $kode)
    {
        return $query->where('kode_icd10', 'like', $kode . '%');
    }

    public function scopeDirujuk($query)
    {
        return $query->where('rujukan', '!=', 'Tidak');
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return UserScope::visibleTo($query);
    }

    public function scopeBpjs($query)
    {
        return $query->where('cara_bayar', 'BPJS');
    }

    public function scopeUmum($query)
    {
        return $query->where('cara_bayar', 'Umum');
    }
}
