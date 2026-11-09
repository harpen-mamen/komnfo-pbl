<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('workflow_logs', function (Blueprint $table) {
            // Preserve batch references and their indexes when changing nullability.
            $table->dropForeign(['batch_id']);
            $table->unsignedBigInteger('batch_id')->nullable()->change();
            $table->foreign('batch_id')->references('id')->on('submission_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // No-op
    }
};
