<?php

namespace App\Models\Concerns;

use App\Models\SubmissionBatch;
use App\Models\SubmissionBatchItem;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSubmissionBatchApproval
{
    public static function bootHasSubmissionBatchApproval(): void
    {
        static::saving(function (Model $model): void {
            if ($model->exists && $model->hasApprovedSubmissionBatch()) {
                throw new AuthorizationException('Entity sudah berada dalam batch approved dan bersifat read-only.');
            }
        });

        static::deleting(function (Model $model): void {
            if ($model->hasApprovedSubmissionBatch()) {
                throw new AuthorizationException('Entity sudah berada dalam batch approved dan tidak dapat dihapus.');
            }
        });
    }

    public function submissionBatchItems(): MorphMany
    {
        return $this->morphMany(SubmissionBatchItem::class, 'entity', 'entity_type', 'entity_id');
    }

    public function latestSubmissionBatchItem(): MorphOne
    {
        return $this->morphOne(SubmissionBatchItem::class, 'entity', 'entity_type', 'entity_id')
            ->latestOfMany('submission_batch_id');
    }

    public function hasApprovedSubmissionBatch(): bool
    {
        return $this->submissionBatchItems()
            ->whereHas('submissionBatch', fn (Builder $batch) => $batch->where('status', SubmissionBatch::STATUS_APPROVED))
            ->exists();
    }

    public function scopeInApprovedBatch(Builder $query): Builder
    {
        $model = $query->getModel();
        $qualifiedKey = $model->getQualifiedKeyName();
        $entityType = $model->getMorphClass();

        return $query->whereExists(function ($exists) use ($qualifiedKey, $entityType) {
            $exists->selectRaw('1')
                ->from('submission_batch_items as sbi')
                ->join('submission_batches as sb', 'sb.id', '=', 'sbi.submission_batch_id')
                ->whereColumn('sbi.entity_id', $qualifiedKey)
                ->where('sbi.entity_type', $entityType)
                ->where('sb.status', SubmissionBatch::STATUS_APPROVED)
                ->whereNotNull('sb.verified_by')
                ->whereNotNull('sb.verified_at')
                ->where('sb.is_published', true)
                ->whereNull('sb.deleted_at')
                ->whereRaw(
                    'sb.id = (
                        select sb_latest.id
                        from submission_batch_items as sbi_latest
                        inner join submission_batches as sb_latest on sb_latest.id = sbi_latest.submission_batch_id
                        where sbi_latest.entity_type = ?
                          and sbi_latest.entity_id = sbi.entity_id
                          and sb_latest.deleted_at is null
                        order by sb_latest.created_at desc, sb_latest.id desc
                        limit 1
                    )',
                    [$entityType]
                );
        });
    }

    public function scopeWithLatestSubmissionBatchStatus(Builder $query, string|array $statuses): Builder
    {
        $statusList = is_array($statuses) ? $statuses : [$statuses];

        return $query->whereHas('latestSubmissionBatchItem.submissionBatch', fn (Builder $batch) => $batch->whereIn('status', $statusList));
    }
}
