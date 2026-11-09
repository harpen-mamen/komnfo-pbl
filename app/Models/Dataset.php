<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Dataset (FASE 1 - Prinsip 13 + SDI Compliance)
 *
 * Definisi dataset per OPD. Setiap dataset punya:
 * - fields: definisi kolom dinamis (DatasetField)
 * - values: nilai time-series (DatasetValue)
 *
 * SDI Compliance (Perpres 39/2019 + Perbup 30/2024):
 * - kode_sdi: kode di Buku Sektoral (3.12, dst)
 * - category_id: 32 Urusan Pemerintah
 * - walidata_id: User Walidata
 * - pembina_data: BPS / DPUPR / Kominfo
 * - jadwal_rilis: tanggal rilis publik
 * - published_at: waktu terakhir di-publish
 */
class Dataset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "datasets";

    protected $fillable = [
        "opd_id",
        "category_id",
        "kode",
        "kode_sdi",
        "nama",
        "deskripsi",
        "kategori",
        "period_type",
        "geometry_type",
        "is_active",
        "requires_verification",
        "config",
        "kontak_produsen",
        "cakupan_wilayah",
        "bahasa",
        "format_data",
        "lisensi",
        "sumber_data",
        "klasifikasi_standar",
        "tags",
        "url_akses",
        "status_aktif",
        "pj_data_nama",
        "pj_data_nip",
        "walidata_id",
        "pembina_data",
        "jadwal_rilis",
        "published_at",
        "created_by",
    ];

    protected $casts = [
        "is_active" => "boolean",
        "requires_verification" => "boolean",
        "config" => "array",
        "tags" => "array",
        "jadwal_rilis" => "date",
        "published_at" => "datetime",
    ];

    public const PERIOD_TAHUNAN = "tahunan";
    public const PERIOD_SEMESTERAN = "semesteran";
    public const PERIOD_TRIWULANAN = "triwulanan";
    public const PERIOD_BULANAN = "bulanan";
    public const PERIOD_MASTER = "master";

    public const LISENSI_CCBY = "cc_by_4";
    public const LISENSI_OPEN = "open_data";
    public const LISENSI_TERBATAS = "terbatas";
    public const LISENSI_INTERNAL = "internal";

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DatasetCategory::class, "category_id");
    }

    public function walidata(): BelongsTo
    {
        return $this->belongsTo(User::class, "walidata_id");
    }

    public function fields(): HasMany
    {
        return $this->hasMany(DatasetField::class)->orderBy("urutan");
    }

    public function values(): HasMany
    {
        return $this->hasMany(DatasetValue::class);
    }

    public function publishedValues(): HasMany
    {
        return $this->hasMany(DatasetValue::class)->where("status", "published");
    }
}
