<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_batches', function (Blueprint $table) {
            $table->id();
            $table->string('submission_number')->unique();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->string('semester', 32)->nullable();
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->string('status', 32)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('kecamatan_id');
            $table->index('status');
            $table->index(['semester', 'tahun']);
        });

        Schema::create('submission_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_batch_id')->constrained('submission_batches')->cascadeOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->timestamps();

            $table->index('submission_batch_id');
            $table->index(['entity_type', 'entity_id']);
            $table->unique(['submission_batch_id', 'entity_type', 'entity_id'], 'submission_batch_items_unique');
        });

        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->foreignId('submission_batch_id')->nullable()->after('id')->constrained('submission_batches')->nullOnDelete();
            $table->unique('submission_batch_id', 'workflow_instances_submission_batch_unique');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->dropUnique('workflow_instances_submission_batch_unique');
            $table->dropForeign(['submission_batch_id']);
            $table->dropColumn('submission_batch_id');
        });

        Schema::dropIfExists('submission_batch_items');
        Schema::dropIfExists('submission_batches');
    }
};
