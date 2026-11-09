<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REFACTOR: Gabung workflow_instances + workflow_histories jadi 1 tabel workflow_logs
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('submission_batches')->cascadeOnDelete();
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->notNull();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['batch_id'], 'wf_logs_batch_idx');
            $table->index(['to_status'], 'wf_logs_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
    }
};
