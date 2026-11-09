<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ProgramBantuan (FASE 1 - Prinsip 6)
 *
 * Master program bantuan (BLT, PKH, dll). Yang menerima bantuan
 * adalah INDIVIDU atau KELUARGA, bukan rumah.
 *
 * Setiap program punya:
 * - jenis: uang, sembako, layanan, voucher, lainnya
 * - periode_pemberian: sekali, bulanan, dst
 * - syarat: kriteria penerima (JSON)
 */
class ProgramBantuan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'program_bantuans';

    protected $fillable = [
        'opd_id',
        'kode',
        'nama',
        'deskripsi',
        'jenis',
        'nominal',
        'satuan',
        'periode_pemberian',
        'tahun_mulai',
        'tahun_selesai',
        'is_active',
        'syarat',
        'source_attribution_id',
        'created_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'is_active' => 'boolean',
        'syarat' => 'array',
    ];

    public const JENIS_UANG = 'uang';
    public const JENIS_SEMBAKO = 'sembako';
    public const JENIS_LAYANAN = 'layanan';
    public const JENIS_VOUCHER = 'voucher';
    public const JENIS_LAINNYA = 'lainnya';

    public const PERIODE_SEKALI = 'sekali';
    public const PERIODE_BULANAN = 'bulanan';
    public const PERIODE_TRIWULANAN = 'triwulanan';
    public const PERIODE_SEMESTERAN = 'semesteran';
    public const PERIODE_TAHUNAN = 'tahunan';

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function penerimaans(): HasMany
    {
        return $this->hasMany(PenerimaBantuan::class);
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
