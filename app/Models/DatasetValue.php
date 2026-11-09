<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DatasetValue (FASE 1 - Prinsip 13, 15 + SDI Workflow)
 *
 * Nilai time-series per Dataset. Setiap value terikat ke:
 * - dataset_id
 * - tahun + periode_label
 * - wilayah (polymorphic: kabupaten/kecamatan/desa)
 * - source_attribution
 * - submission_batch (workflow)
 * - status workflow (draft -> submitted -> verified -> published)
 */
class DatasetValue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "dataset_values";

    protected $fillable = [
        "dataset_id",
        "tahun",
        "periode_label",
        "wilayah_type",
        "wilayah_id",
        "values",
        "status",
        "status_verifikasi",
        "verified_at",
        "verified_by",
        "published_at",
        "notes",
        "source_attribution_id",
        "submission_batch_id",
        "dataset_submission_id",
        "created_by",
    ];

    protected $casts = [
        "tahun" => "integer",
        "values" => "array",
        "verified_at" => "datetime",
        "published_at" => "datetime",
    ];

    public const STATUS_DRAFT = "draft";
    public const STATUS_SUBMITTED = "submitted";
    public const STATUS_VERIFIED = "verified";
    public const STATUS_REJECTED = "rejected";
    public const STATUS_PUBLISHED = "published";
    public const STATUS_REVISION = "revision";

    public const WILAYAH_KABUPATEN = "kabupaten";
    public const WILAYAH_KECAMATAN = "kecamatan";
    public const WILAYAH_DESA = "desa";

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function wilayah(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "verified_by");
    }

    public function dataSubmission(): BelongsTo
    {
        return $this->belongsTo(DatasetSubmission::class, 'dataset_submission_id');
    }

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'submission_batch_id');
    }

    /**
     * Submit data ke Walidata untuk verifikasi
     */
    public function submit(): bool
    {
        $this->status = self::STATUS_SUBMITTED;
        $this->status_verifikasi = self::STATUS_SUBMITTED;
        return $this->save();
    }

    /**
     * Verify data oleh Walidata
     */
    public function verify(User $walidata, ?string $notes = null): bool
    {
        $this->status = self::STATUS_VERIFIED;
        $this->status_verifikasi = self::STATUS_VERIFIED;
        $this->verified_at = now();
        $this->verified_by = $walidata->id;
        $this->notes = $notes;
        return $this->save();
    }

    /**
     * Publish data ke publik
     */
    public function publish(): bool
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->status_verifikasi = self::STATUS_PUBLISHED;
        $this->published_at = now();
        return $this->save();
    }

    /**
     * Reject data
     */
    public function reject(User $walidata, string $notes): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->status_verifikasi = self::STATUS_REJECTED;
        $this->verified_at = now();
        $this->verified_by = $walidata->id;
        $this->notes = $notes;
        return $this->save();
    }
}
