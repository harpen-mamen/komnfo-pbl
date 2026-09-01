<?php

namespace App\Policies;

use App\Models\SubmissionBatch;
use App\Models\User;
use App\Support\AppRole;

/**
 * A13: Authorization policy for SubmissionBatch.
 *
 * - Kabupaten level (admin, super_admin, admin_kominfo): full access
 * - Admin kecamatan: can view batches in their kecamatan, can submit/forward
 * - Admin desa: can create and submit batches for their desa
 * - OPD users: limited to their own batches
 */
class SubmissionBatchPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasAnyRole([AppRole::ADMIN->value, AppRole::SUPER_ADMIN->value, AppRole::ADMIN_KOMINFO->value])
            ? true
            : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(AppRole::ADMIN_KECAMATAN->value) && $user->kecamatan_id !== null
            || $user->hasRole(AppRole::ADMIN_DESA->value) && $user->desa_id !== null
            || $this->isOpdUser($user);
    }

    public function view(User $user, SubmissionBatch $batch): bool
    {
        if ($user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return $user->kecamatan_id !== null
                && (int) $batch->kecamatan_id === (int) $user->kecamatan_id;
        }

        if ($user->hasRole(AppRole::ADMIN_DESA->value)) {
            return $user->desa_id !== null
                && (int) $batch->created_by === (int) $user->id;
        }

        if ($this->isOpdUser($user)) {
            return (int) $batch->created_by === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(AppRole::ADMIN_DESA->value) && $user->desa_id !== null
            || $user->hasRole(AppRole::ADMIN_KECAMATAN->value) && $user->kecamatan_id !== null
            || $this->isOpdUser($user);
    }

    public function submit(User $user, SubmissionBatch $batch): bool
    {
        return (int) $batch->created_by === (int) $user->id;
    }

    public function forward(User $user, SubmissionBatch $batch): bool
    {
        if (! $user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return false;
        }

        return $user->kecamatan_id !== null
            && (int) $batch->kecamatan_id === (int) $user->kecamatan_id;
    }

    public function approve(User $user, SubmissionBatch $batch): bool
    {
        // Only kabupaten kominfo can approve (already handled by before, but explicit for clarity).
        return false;
    }

    public function reject(User $user, SubmissionBatch $batch): bool
    {
        return false;
    }

    public function update(User $user, SubmissionBatch $batch): bool
    {
        return (int) $batch->created_by === (int) $user->id
            && in_array($batch->status, [SubmissionBatch::STATUS_DRAFT], true);
    }

    public function delete(User $user, SubmissionBatch $batch): bool
    {
        return (int) $batch->created_by === (int) $user->id
            && $batch->status === SubmissionBatch::STATUS_DRAFT;
    }

    private function isOpdUser(User $user): bool
    {
        // Any OPD role (admin_/operator_ for any module) can create batches for their data.
        $opdRoles = collect(AppRole::cases())
            ->filter(fn (AppRole $r) => $r->isOpdLevel())
            ->map(fn (AppRole $r) => $r->value)
            ->all();

        return $user->hasAnyRole($opdRoles);
    }
}
