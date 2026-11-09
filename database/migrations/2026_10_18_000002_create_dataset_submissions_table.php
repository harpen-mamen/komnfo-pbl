<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dataset_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('submitted');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->index(['opd_id', 'status', 'submitted_at'], 'dataset_submissions_opd_status_idx');
        });

        Schema::table('dataset_values', function (Blueprint $table) {
            $table->foreignId('dataset_submission_id')->nullable()->after('submission_batch_id')
                ->constrained('dataset_submissions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dataset_values', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dataset_submission_id');
        });
        Schema::dropIfExists('dataset_submissions');
    }
};
