<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PenerimaBantuan (FASE 1 - Prinsip 6)
 *
 * Relasi antara ProgramBantuan dengan Penduduk atau Keluarga.
 * Polymorphic: `penerima_type` bisa 'penduduk' atau 'keluarga'.
 *
 * Memisahkan "rumah penerima bantuan" dari "individu/kk penerima"
 * (Prinsip 6: yang menerima bantuan adalah INDIVIDU atau KELUARGA,
 * bukan rumah).
 */
class PenerimaBantuan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'penerima_bantuans';

    protected $fillable = [
        'program_bantuan_id',
        'penerima_type',
        'penerima_id',
        'tahun',
        'periode_label',
        'periode_id',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'nominal_diterima',
        'keterangan',
        'dokumen_pendukung',
        'source_attribution_id',
        'created_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'nominal_diterima' => 'decimal:2',
        'dokumen_pendukung' => 'array',
    ];

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    public const PENERIMA_PENDUDUK = 'penduduk';
    public const PENERIMA_KELUARGA = 'keluarga';

    public function programBantuan(): BelongsTo
    {
        return $this->belongsTo(ProgramBantuan::class);
    }

    public function penerima(): MorphTo
    {
        return $this->morphTo();
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePelaporan::class, 'periode_id');
    }

    public function sourceAttribution(): BelongsTo
    {
        return $this->belongsTo(SourceAttribution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
