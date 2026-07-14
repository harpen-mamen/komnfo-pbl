<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionBatch extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const MODULE_RUMAH = 'rumah';
    public const MODULE_KELUARGA = 'keluarga';
    public const MODULE_PENDUDUK = 'penduduk';
    public const MODULE_KESEHATAN = 'kesehatan';
    public const MODULE_PENDIDIKAN = 'pendidikan';
    public const MODULE_MIXED = 'mixed';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'menunggu_verifikasi';
    public const STATUS_IN_REVIEW = 'sedang_diverifikasi';
    public const STATUS_REVISION = 'perlu_revisi';
    public const STATUS_APPROVED = 'disetujui';
    public const STATUS_REJECTED = 'ditolak';
    public const STATUS_ARCHIVED = 'diarsipkan';

    protected $table = 'submission_batches';

    protected $fillable = [
        'module',
        'submission_number',
        'kecamatan_id',
        'periode_bulan',
        'semester',
        'periode_tahun',
        'package_signature',
        'package_keys',
        'package_summary',
        'status',
        'notes',
        'created_by',
        'submitted_at',
        'verified_at',
        'verified_by',
        'payload_snapshot',
        'version',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'package_keys' => 'array',
        'package_summary' => 'array',
        'payload_snapshot' => 'array',
        'version' => 'integer',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubmissionBatchItem::class);
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeForPeriod(Builder $query, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null): Builder
    {
        return $query
            ->when($periodeBulan !== null, fn (Builder $query) => $query->where('periode_bulan', $periodeBulan))
            ->when($semester !== null, fn (Builder $query) => $query->where('semester', $semester))
            ->when($periodeTahun !== null, fn (Builder $query) => $query->where('periode_tahun', $periodeTahun));
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeLatestByModule(Builder $query, string $module, int $kecamatanId, ?int $periodeBulan = null, ?string $semester = null, ?int $periodeTahun = null): Builder
    {
        return $query
            ->forModule($module)
            ->where('kecamatan_id', $kecamatanId)
            ->forPeriod($periodeBulan, $semester, $periodeTahun)
            ->orderByDesc('verified_at')
            ->orderByDesc('submitted_at')
            ->orderByDesc('created_at');
    }

    public static function moduleLabel(?string $module): string
    {
        return match ($module) {
            self::MODULE_RUMAH => 'Rumah',
            self::MODULE_KELUARGA => 'Keluarga',
            self::MODULE_PENDUDUK => 'Penduduk',
            self::MODULE_KESEHATAN => 'Kesehatan',
            self::MODULE_PENDIDIKAN => 'Pendidikan',
            self::MODULE_MIXED => 'Mixed Submission',
            default => $module ?? '-',
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_IN_REVIEW => 'Sedang Diverifikasi',
            self::STATUS_REVISION => 'Perlu Revisi',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_ARCHIVED => 'Diarsipkan',
            default => $status ?? '-',
        };
    }
}
