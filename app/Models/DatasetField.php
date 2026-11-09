<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DatasetField (FASE 1 - Prinsip 13 + SDI Pasal 4 Perbup 30/2024)
 *
 * Kolom dinamis per Dataset. Setiap field punya:
 * - nama: internal name (e.g., "jumlah_dokter")
 * - label: display name
 * - tipe: string|integer|decimal|boolean|date|reference|...
 *
 * 5 Elemen Standar Data (Pasal 4 Perbup 30/2024):
 * - konsep: ide/gagasan di balik data
 * - definisi: penjelasan batasan data
 * - klasifikasi: penggolongan data
 * - ukuran: unit pengukuran
 * - satuan: satuan (Jiwa, Unit, Rp, dll)
 *
 * Properti tambahan:
 * - is_dimension: TRUE untuk dimensi (group by)
 * - is_primary_key: TRUE untuk PK
 * - lookup_source: reference ke tabel lain
 */
class DatasetField extends Model
{
    use HasFactory;

    protected $table = "dataset_fields";

    protected $fillable = [
        "dataset_id",
        "nama",
        "label",
        "tipe",
        "reference_target",
        "required",
        "validation_rules",
        "options",
        "konsep",
        "definisi",
        "klasifikasi",
        "ukuran",
        "satuan",
        "is_dimension",
        "is_primary_key",
        "lookup_source",
        "urutan",
        "is_searchable",
    ];

    protected $casts = [
        "required" => "boolean",
        "is_searchable" => "boolean",
        "is_dimension" => "boolean",
        "is_primary_key" => "boolean",
        "validation_rules" => "array",
        "options" => "array",
        "urutan" => "integer",
    ];

    public const TIPE_STRING = "string";
    public const TIPE_TEXT = "text";
    public const TIPE_INTEGER = "integer";
    public const TIPE_DECIMAL = "decimal";
    public const TIPE_BOOLEAN = "boolean";
    public const TIPE_DATE = "date";
    public const TIPE_ENUM = "enum";

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }
}
