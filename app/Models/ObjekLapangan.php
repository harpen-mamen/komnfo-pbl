<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ObjekLapangan (FASE 1 - Prinsip 7, 8, 19)
 *
 * Root entity untuk semua objek yang dikumpulkan Admin Desa via mobile.
 *
 * Polymorphic-style: tipe objek ditentukan oleh `objek_lapangan_type_id`,
 * atribut spesifik per tipe disimpan di `attributes` (JSON).
 *
 * Contoh: Rumah, Sekolah, Kantor, Jalan, Jembatan, Posyandu, dll.
 *
 * Geometry:
 * - POINT: lat/lng (default untuk most types)
 * - LINESTRING/POLYGON: `geometry_wkt` (WKT format, requires spatial extension)
 */
class ObjekLapangan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'objek_lapangans';

    protected $fillable = [
        'objek_lapangan_type_id',
        'desa_id',
        'dusun_id',
        'nama',
        'kode',
        'alamat',
        'koordinat_lat',
        'koordinat_lng',
        'geometry_wkt',
        'attributes',
        'status_verifikasi',
        'verified_at',
        'verified_by',
        'verification_notes',
        'source_attribution_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
        'attributes' => 'array',
        'verified_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIAJUKAN = 'diajukan';
    public const STATUS_DIVERIFIKASI = 'diverifikasi';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_REVISI = 'revisi';

    public function type(): BelongsTo
    {
        return $this->belongsTo(ObjekLapanganType::class, 'objek_lapangan_type_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function sourceAttribution(): BelongsTo
    {
        return $this->belongsTo(SourceAttribution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get attribute value from JSON attributes bag.
     */
    public function getAttributeValue(string $key, $default = null)
    {
        return data_get($this->attributes, $key, $default);
    }

    /**
     * Check if this object is verified.
     */
    public function isVerified(): bool
    {
        return $this->status_verifikasi === self::STATUS_DIVERIFIKASI;
    }

    /**
     * Check if this is a point geometry.
     */
    public function isPoint(): bool
    {
        return $this->type?->geometry_type === ObjekLapanganType::GEOMETRY_POINT;
    }
}
