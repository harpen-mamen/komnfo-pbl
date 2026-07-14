<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\PanelAccess;
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
        'kecamatan_id',
        'desa_id',
        'jumlah_siswa',
        'laki_laki',
        'perempuan',
        'putus_sekolah',
        'lulus',
        'naik_kelas',
        'tidak_naik',
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
        $user = PanelAccess::user();

        if (PanelAccess::isAdminKecamatan()) {
            $query->where('kecamatan_id', $user?->kecamatan_id);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
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

}
