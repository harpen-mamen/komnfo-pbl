<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\PanelAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class DataKesehatanBulanan extends Model
{
    use HasFactory;
    use HasSubmissionBatchApproval;

    public const STATUS = [
        'Terkirim' => 'Terkirim',
        'Terverifikasi' => 'Terverifikasi',
        'Perlu Revisi' => 'Perlu Revisi',
    ];

    protected $table = 'data_kesehatan_bulanans';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'periode_pelaporan_id',
        'puskesmas',
        'kecamatan_id',
        'desa_id',
        'jumlah_balita',
        'jumlah_ibu_hamil',
        'stunting',
        'gizi_buruk',
        'disabilitas',
        'kematian',
        'kelahiran',
        'bpjs_aktif',
        'bpjs_tidak_aktif',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'jumlah_balita' => 'integer',
        'jumlah_ibu_hamil' => 'integer',
        'stunting' => 'integer',
        'gizi_buruk' => 'integer',
        'disabilitas' => 'integer',
        'kematian' => 'integer',
        'kelahiran' => 'integer',
        'bpjs_aktif' => 'integer',
        'bpjs_tidak_aktif' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DataKesehatanBulanan $model): void {
            $model->created_by ??= Auth::id();
            $model->updated_by ??= Auth::id();
        });

        static::saving(function (DataKesehatanBulanan $model): void {
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
