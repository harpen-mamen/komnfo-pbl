<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\AdminKecamatan;

class ImportPendudukLog extends Model
{
    use HasFactory;

    protected $table = 'import_gagal';

    protected $fillable = [
        'desa_id',
        'kecamatan_id',
        'nomor_kk',
        'nik',
        'nama',
        'baris',
        'alasan',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        if (AdminKecamatan::isAdminKecamatan()) {
            $query->where(function (Builder $query): void {
                $query
                    ->where('kecamatan_id', AdminKecamatan::kecamatanId())
                    ->orWhereHas('desa', fn (Builder $desa) => $desa->where('kecamatan_id', AdminKecamatan::kecamatanId()));
            });
        }

        return $query;
    }
}
