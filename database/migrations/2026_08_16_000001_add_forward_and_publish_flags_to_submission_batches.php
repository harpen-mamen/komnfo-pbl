<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CRITICAL FIX — SIPANDU Workflow Alignment:
     *
     * Forward tracking (C2-fix companion):
     *   Before: Forward was detected via `catatan LIKE '%forward%'` — fragile string matching.
     *   After:  Explicit `forwarded_at` + `forwarded_by` columns on submission_batches.
     *
     * Idempotent publish (C1-fix):
     *   Before: publish() called directly inside WorkflowEngine::transition() on APPROVED,
     *            with no atomic guard — race condition if approve() called twice.
     *   After:  `is_published` boolean flag set atomically inside the same DB transaction
     *            as the APPROVED status change, preventing double-publish.
     *
     * Architecture enforcement:
     *   - Admin Kecamatan can ONLY forward master-data batches to Kominfo.
     *   - Only Admin Kominfo (or kabupaten admins) can final-approve and trigger publish.
     *   - Publish is idempotent: second publish calls return early via `is_published` check.
     */
    public function up(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            // Forward tracking — replaces fragile `catatan LIKE '%forward%'` check
            $table->timestamp('forwarded_at')->nullable()->after('verified_by');
            $table->foreignId('forwarded_by')
                ->nullable()
                ->after('forwarded_at')
                ->constrained('users')
                ->nullOnDelete();

            // Idempotent publish guard — prevents double-publish race condition
            $table->boolean('is_published')->default(false)->after('forwarded_by');

            // Compound index for the most common query path:
            // "all unpublished APPROVED batches from this kecamatan for this module/period"
            $table->index(
                ['status', 'is_published', 'kecamatan_id'],
                'submission_batches_workflow_queue_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('submission_batches', function (Blueprint $table) {
            $table->dropIndex('submission_batches_workflow_queue_idx');
            $table->dropForeign(['forwarded_by']);
            $table->dropColumn(['forwarded_at', 'forwarded_by', 'is_published']);
        });
    }
};
