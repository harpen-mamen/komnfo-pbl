<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\UserScope;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

class Penduduk extends Model
{
    use HasFactory;
    use HasSubmissionBatchApproval;

    public const JENIS_KELAMIN = ['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan'];
    public const AGAMA = ['Islam' => 'Islam', 'Kristen' => 'Kristen', 'Katolik' => 'Katolik', 'Hindu' => 'Hindu', 'Budha' => 'Budha', 'Konghucu' => 'Konghucu'];
    public const STATUS_PERKAWINAN = ['Belum Kawin' => 'Belum Kawin', 'Kawin' => 'Kawin', 'Cerai Hidup' => 'Cerai Hidup', 'Cerai Mati' => 'Cerai Mati'];
    public const PENDIDIKAN = ['Tidak Sekolah' => 'Tidak Sekolah', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA', 'D1' => 'D1', 'D2' => 'D2', 'D3' => 'D3', 'S1' => 'S1', 'S2' => 'S2', 'S3' => 'S3'];
    public const STATUS_DALAM_KELUARGA = ['Kepala Keluarga' => 'Kepala Keluarga', 'Istri' => 'Istri', 'Anak' => 'Anak', 'Menantu' => 'Menantu', 'Cucu' => 'Cucu', 'Orang Tua' => 'Orang Tua', 'Lainnya' => 'Lainnya'];
    public const STATUS_PENDUDUK = ['Tetap' => 'Tetap', 'Domisili' => 'Domisili'];

    protected $table = 'penduduk';

    protected $fillable = [
        'keluarga_id',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'status_perkawinan',
        'pendidikan',
        'pekerjaan',
        'penghasilan',
        'status_dalam_keluarga',
        'nomor_hp',
        'status_penduduk',
        'created_by',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'penghasilan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn (Penduduk $penduduk) => $penduduk->created_by ??= Auth::id());
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        // SPEC-004: Single source of truth — UserScope::visibleTo().
        return UserScope::visibleTo($query);
    }

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * FASE 1: Source attribution (polymorphic).
     *
     * Setiap penduduk memiliki track sumber data:
     - 'dukcapil_import' = import dari Dukcapil (default)
     - 'import_excel' = import via Excel
     */
    public function sourceAttribution(): MorphOne
    {
        return $this->morphOne(SourceAttribution::class, 'sourceable');
    }

    public function getUmurAttribute(): ?int
    {
        return $this->tanggal_lahir instanceof CarbonInterface ? $this->tanggal_lahir->age : null;
    }
}
