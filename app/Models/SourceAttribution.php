<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Source Attribution (FASE 1 - Prinsip 16)
 *
 * Setiap data penting harus dapat diketahui sumbernya:
 * - source_type: tipe sumber
 * - source_opd_id: OPD asal
 * - source_user_id: user penginput
 * - source_reference: referensi eksternal
 * - source_metadata: metadata tambahan (JSON)
 *
 * Polymorphic: di-attach ke SEMUA entity.
 */
class SourceAttribution extends Model
{
    use HasFactory;

    protected $table = 'source_attributions';

    protected $fillable = [
        'sourceable_type',
        'sourceable_id',
        'source_type',
        'source_opd_id',
        'source_user_id',
        'source_reference',
        'source_metadata',
    ];

    protected $casts = [
        'source_metadata' => 'array',
    ];

    // Source type constants (mirror migration enum)
    public const TYPE_DUKCAPIL_IMPORT = 'dukcapil_import';
    public const TYPE_DINAS_MASTER = 'dinas_master';
    public const TYPE_DINAS_PERIODIK = 'dinas_periodik';
    public const TYPE_PUSKES_PERIODIK = 'puskes_periodik';
    public const TYPE_SEKOLAH_PERIODIK = 'sekolah_periodik';
    public const TYPE_FIELD_COLLECTION = 'field_collection';
    public const TYPE_CSR_PROGRAM = 'csr_program';
    public const TYPE_BANTUAN_PEMERINTAH = 'bantuan_pemerintah';
    public const TYPE_IMPORT_EXCEL = 'import_excel';
    public const TYPE_API_EKSTERNAL = 'api_eksternal';
    public const TYPE_LAINNYA = 'lainnya';

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function sourceOpd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'source_opd_id');
    }

    public function sourceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_user_id');
    }
}
