<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ObjekLapanganType (FASE 1 - Prinsip 7, 8, 19)
 *
 * Configurator untuk tipe objek lapangan:
 * - RUMAH (POINT)
 * - SEKOLAH (POINT)
 * - KANTOR (POINT)
 * - JALAN (LINESTRING)
 * - JEMBATAN (POINT)
 * - DRAINASE (LINESTRING)
 * - POSYANDU (POINT)
 * - TEMPAT_IBADAH (POINT)
 * - PASAR (POLYGON)
 * - dll
 *
 * `field_schema` (JSON) mendefinisikan field dinamis per tipe.
 */
class ObjekLapanganType extends Model
{
    use HasFactory;

    protected $table = 'objek_lapangan_types';

    protected $fillable = [
        'kode',
        'nama',
        'geometry_type',
        'icon',
        'color',
        'is_active',
        'field_schema',
        'validation_rules',
        'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'field_schema' => 'array',
        'validation_rules' => 'array',
        'urutan' => 'integer',
    ];

    public const GEOMETRY_POINT = 'point';
    public const GEOMETRY_LINESTRING = 'linestring';
    public const GEOMETRY_POLYGON = 'polygon';

    public function objekLapangans(): HasMany
    {
        return $this->hasMany(ObjekLapangan::class);
    }
}
